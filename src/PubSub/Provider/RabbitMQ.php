<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider;

use Chocofamily\PubSub\Exceptions\ConnectionException;
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
     * Кол-во попыток публикации сообщения
     */
    const REDELIVERY_COUNT = 5;

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

    /** @var OutputMessage */
    private $message;

    /** @var callable */
    private $callback;

    private $unacknowledged = 0;

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

    /**
     * Опубликовать сообщение
     *
     */
    public function publish()
    {
        $try = static::REDELIVERY_COUNT;
        while ($try--) {
            try {
                $channel = $this->exchangeDeclare();
                $channel->basic_publish(
                    $this->message->getPayload(),
                    $this->currentExchange->getExchange(),
                    $this->currentExchange->getRoutes()[0]
                );
            } catch (AMQPRuntimeException $e) {
                $this->clear();
                $this->connection->reconnect();
                continue;
            }
            break;
        }
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
        $deliveryChannel = $msg->delivery_info['channel'];

        $isNoAck = $this->getConfig('no_ack', false);

        $message = new InputMessage($msg);

        try {
            call_user_func($this->callback, $message->getHeaders(), $message->getPayload());
        } catch (RetryException $e) {
            // todo написать middleware для repeater
            if ($isNoAck == false) {
                $repeat = $this->repeater->isRepeatable($message);
                $deliveryChannel->basic_reject($msg->delivery_info['delivery_tag'], $repeat);

                return;
            }
        } catch (\Exception $e) {
            $deliveryChannel->basic_reject($msg->delivery_info['delivery_tag'], false);

            throw $e;
        }

        $this->unacknowledged++;
        if ($isNoAck == false and $this->unacknowledged == $this->getConfig('prefetch_count', 1)) {
            $this->unacknowledged = 0;
            $deliveryChannel->basic_ack(
                $msg->delivery_info['delivery_tag'],
                $this->getConfig('prefetch_count', 1) > 1
            );
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
     */
    public function setMessage(array $message, array $headers = [])
    {
        $defaultHeaders = ['app_id' => $this->getConfig('app_id')];
        $headers        = array_merge($headers, $defaultHeaders);
        $this->message  = new OutputMessage($message, $headers);
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
