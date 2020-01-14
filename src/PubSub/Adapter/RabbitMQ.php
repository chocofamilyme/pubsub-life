<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Adapter;

use Chocofamily\PubSub\Exceptions\ConnectionException;
use Chocofamily\PubSub\Message\Repeater;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Exception\AMQPRuntimeException;

use Chocofamily\PubSub\Exceptions\RetryException;
use Chocofamily\PubSub\Adapter\RabbitMQ\Message\Output as OutputMessage;
use Chocofamily\PubSub\Adapter\RabbitMQ\Message\Input as InputMessage;

/**
 * Class RabbitMQ
 * Работает с брокером сообщений RabbitMQ
 *
 * Документация по брокеру:
 *
 * https://www.rabbitmq.com/documentation.html
 *
 * @package Chocofamily\PubSub\Provider
 */
class RabbitMQ extends AbstractAdapter
{
    /** @var AMQPStreamConnection */
    private $connection;

    /** @var array */
    private $channels = [];

    /** @var callable */
    private $callback;

    /** @var int Кол-во сообщений, требующих подтверждения */
    private $unacknowledgedMessages = 0;

    /** @var Repeater */
    private $repeater;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->repeater = new Repeater($this);
    }

    /**
     * @throws ConnectionException
     */
    public function connect()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $this->getConfig('host'),
                $this->getConfig('port'),
                $this->getConfig('user'),
                $this->getConfig('password'),
                $this->getConfig('vhost', '/'),
                $this->getConfig('insist', false),
                'AMQPLAIN',
                $this->getConfig('login_response'),
                $this->getConfig('locale', 'en_US'),
                $this->getConfig('connection_timeout', 3.0),
                $this->getConfig('read_write_timeout', 3.0),
                $this->getConfig('context'),
                $this->getConfig('keepalive', false),
                $this->getConfig('heartbeat', 0)
            );
        } catch (\Exception $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws \Exception
     */
    public function disconnect()
    {
        if ($this->connection->isConnected()) {
            $this->clear();
            $this->connection->close();
        }
    }

    public function publish(array $data, array $headers = [], array $params = [])
    {
        $this->repeater->inject($headers);
        $params['application_headers'] = $headers;

        $message = $this->createOutputMessage($data, $params);

        do {
            $keepTrying = false;
            try {
                $channel = $this->exchangeDeclare();
                $channel->basic_publish(
                    $message->getPayload(),
                    $this->route->getExchange(),
                    $this->route->getRoutes()[0]
                );
            } catch (AMQPRuntimeException $e) {
                $keepTrying = $message->isRepeatable();
                $this->clear();
                $this->connection->reconnect();
            }
        } while ($keepTrying);
    }

    /**
     * @param callable $callback
     *
     * @throws \ErrorException
     */
    public function subscribe(callable $callback)
    {
        $channel = $this->exchangeDeclare();

        $queueData = $channel->queue_declare(
            $this->route->getQueue(),
            false,
            $this->getConfig('durable', true),
            $this->getConfig('exclusive', false),
            false,
            false,
            new AMQPTable($this->getConfig('queue', []))
        );

        $queueName = array_shift($queueData);

        foreach ($this->route->getRoutes() as $route) {
            $channel->queue_bind(
                $queueName,
                $this->route->getExchange(),
                $route
            );
        }

        $channel->basic_qos(
            null,
            $this->getConfig('prefetch_count', 1),
            null
        );

        $channel->basic_consume(
            $queueName,
            $this->route->getConsumer(),
            false,
            $this->getConfig('no_ack', false),
            $this->getConfig('basic_consume_exclusive', false),
            false,
            [$this, 'callbackWrapper']
        );

        $this->callback = $callback;

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, function ($signal) {
                $this->disconnect();
                fwrite(STDERR, "Broker connection close".PHP_EOL);
            });
        }

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    /**
     * Объявление точки входа и канала
     *
     * @return AMQPChannel
     */
    private function exchangeDeclare()
    {
        $exchangeName = $this->route->getExchange();

        if (!isset($this->channels[$exchangeName])) {
            $channel = $this->connection->channel();
            $channel->exchange_declare(
                $exchangeName,
                $this->getConfig('exchange_type', 'topic'),
                $this->getConfig('passive', false),
                $this->getConfig('durable', true),
                $this->getConfig('auto_delete', false)
            );

            $this->channels[$exchangeName] = $channel;
        }

        return $this->channels[$exchangeName];
    }

    /**
     * @param AMQPMessage $msg
     *
     * @throws \Exception
     */
    public function callbackWrapper(AMQPMessage $msg)
    {
        /** @var AMQPChannel $deliveryChannel */
        $deliveryChannel         = $msg->delivery_info['channel'];
        $confirmMsgAutomatically = $this->getConfig('no_ack', false);
        $message                 = new InputMessage($msg);

        try {
            call_user_func($this->callback, $message);
        } catch (RetryException $e) {
            if (!$confirmMsgAutomatically) {
                $deliveryChannel->basic_reject($msg->delivery_info['delivery_tag'], false);
                $this->repeater->resend($message);

                return;
            }
        } catch (\Exception $e) {
            $deliveryChannel->basic_reject($msg->delivery_info['delivery_tag'], false);

            throw $e;
        }

        $this->unacknowledgedMessages++;
        $prefetchCount = $this->getConfig('prefetch_count', 1);

        if (!$confirmMsgAutomatically && $this->unacknowledgedMessages == $prefetchCount) {
            $this->unacknowledgedMessages = 0;
            $deliveryChannel->basic_ack($msg->delivery_info['delivery_tag'], $prefetchCount > 1);
        }
    }

    /**
     * @param array $message
     * @param array $params
     *
     * @return OutputMessage|\Chocofamily\PubSub\OutputMessageInterface
     */
    protected function createOutputMessage(array $message, array $params)
    {
        $params['app_id'] = $this->getConfig('app_id');

        return new OutputMessage($message, $params);
    }

    /**
     * Очистить
     */
    private function clear()
    {
        $this->channels = [];
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    protected function getConfig($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
}
