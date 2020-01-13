<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider;

use Chocofamily\PubSub\Exceptions\ConnectionException;
use Chocofamily\PubSub\MessageInterface;
use Chocofamily\PubSub\RouteInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Exception\AMQPRuntimeException;

use Chocofamily\PubSub\Exceptions\RetryException;
use Chocofamily\PubSub\Provider\RabbitMQ\Message\Output as OutputMessage;
use Chocofamily\PubSub\Provider\RabbitMQ\Message\Input as InputMessage;

/**
 * Class RabbitMQ
 * Работает с брокером сообщений RabbitMQ
 *
 * @package Chocofamily\PubSub\Provider
 */
class RabbitMQ extends AbstractProvider
{
    /**
     * Тип отправки по-умолчанию
     */
    const DEFAULT_EXCHANGE_TYPE = 'topic';

    /** @var bool */
    private $passive = false;

    /**
     * Удаление exchange если нет подключений к нему
     *
     * @var bool
     */
    private $auto_delete = false;

    /** @var AMQPStreamConnection */
    private $connection;

    /** @var RouteInterface */
    private $currentExchange;

    /** @var array */
    private $exchanges = [];

    /** @var array */
    private $channels = [];

    /** @var callable */
    private $callback;

    /** @var int Кол-во сообщений, требующих подтверждения */
    private $unacknowledgedMessages = 0;

    /**
     * @throws ConnectionException
     */
    public function connect()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'],
                $this->config['port'],
                $this->config['user'],
                $this->config['password'],
                $vhost = $this->config['vhost'] ?: '/',
                $insist = $this->config['insist'] ?: false,
                $login_method = 'AMQPLAIN',
                $login_response = $this->config['login_response'] ?: null,
                $locale = $this->config['locale'] ?: 'en_US',
                $connection_timeout = $this->config['connection_timeout'] ?: 3.0,
                $read_write_timeout = $this->config['read_write_timeout'] ?: 3.0,
                $context = $this->config['context'] ?: null,
                $keepalive = $this->config['keepalive'] ?: false,
                $heartbeat = $this->config['heartbeat'] ?: 0
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

    public function publish(MessageInterface $message)
    {
        do {
            $keepTrying = false;
            try {
                $channel = $this->exchangeDeclare();
                $channel->basic_publish(
                    $message->getPayload(),
                    $this->currentExchange->getExchange(),
                    $this->currentExchange->getRoutes()[0]
                );
            } catch (AMQPRuntimeException $e) {
                $keepTrying = $message->isRepeatable();
                $this->clear();
                $this->connection->reconnect();
            }
        } while ($keepTrying);
    }

    /**
     * Подписка на событие
     *
     * @param          $queueNameParam string Имя очереди
     * @param callable $callback       Функция обработки сообщения
     * @param string   $consumerTag    Уникальное имя подписчика
     *
     * @throws \ErrorException
     */
    public function subscribe($queueNameParam, callable $callback, $consumerTag)
    {
        $channel = $this->exchangeDeclare();

        $queueData = $channel->queue_declare(
            $queueNameParam,
            false,
            $this->getConfig('durable', true),
            $this->getConfig('exclusive', false),
            false,
            false,
            new AMQPTable($this->getConfig('queue', []))
        );

        $queueName = array_shift($queueData);

        foreach ($this->currentExchange->getRoutes() as $route) {
            $channel->queue_bind(
                $queueName,
                $this->currentExchange->getExchange(),
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
            $consumerTag,
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
        $key = $this->currentExchange->getExchange();

        if (isset($this->exchanges[$key]) == false) {
            $this->channels[$key] = $this->connection->channel();
            $this->channels[$key]->exchange_declare(
                $this->currentExchange->getExchange(),
                $this->getConfig('exchange_type', self::DEFAULT_EXCHANGE_TYPE),
                $this->passive,
                $this->getConfig('durable', true),
                $this->auto_delete
            );
            $this->exchanges[$this->currentExchange->getExchange()] = true;
        }

        return $this->channels[$key];
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
            call_user_func($this->callback, $message->getHeaders(), $message->getPayload());
        } catch (RetryException $e) {
            if (!$confirmMsgAutomatically) {
                $deliveryChannel->basic_reject($msg->delivery_info['delivery_tag'], false);

                if ($message->isRepeatable()) {
                    $cnt = $message->getHeader('receive_attempts', 0) - 1;
                    $this->publish($this->getMessage($message->getPayload(), $message->getHeaders(), $cnt));
                }

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
     * @param RouteInterface $route
     */
    public function setRoute(RouteInterface $route)
    {
        $this->currentExchange = $route;
    }

    /**
     * @param array $message
     * @param array $headers
     * @param int   $receiveAttempts
     *
     * @return OutputMessage
     */
    public function getMessage(array $message, array $headers, $receiveAttempts = 5)
    {
        $defaultHeaders = ['app_id' => $this->getConfig('app_id')];
        $headers        = array_merge($headers, $defaultHeaders);

        return new OutputMessage($message, $headers, $receiveAttempts);
    }

    /**
     * @param string $key
     * @param        $default
     *
     * @return mixed
     */
    private function getConfig($key, $default = '')
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    /**
     * @param array $params
     */
    public function addConfig(array $params = [])
    {
        $this->config = array_merge($params, $this->config);
    }

    /**
     * Очистить
     */
    private function clear()
    {
        $this->exchanges = [];
        $this->channels  = [];
    }
}
