<?php
/**
 * @package Chocolife.me
 * @author  docxplusgmoon <nurgabylov.d@chocolife.kz>
 */

namespace Helper\PubSub;

use Chocofamily\PubSub\Provider\ProviderInterface;
use Chocofamily\PubSub\RouteInterface;

/**
 * Class DefaultProvider
 *
 * @package Helper\PubSub
 */
class DefaultProvider implements ProviderInterface
{
    public $queue    = [];
    public $exchange = '';

    /**
     * @var string
     */
    public $message = '';

    /**
     * @var array
     */
    public $headers = [];

    public function connect()
    {
    }

    public function disconnect()
    {
    }

    public function publish()
    {
        $data = [
            'message' => $this->message,
            'headers' => $this->headers,
        ];

        $this->queue[$this->exchange] = $data;
    }

    public function subscribe($queueName, callable $callback, $consumerTag = '')
    {

    }

    public function setMessage(array $message, array $headers = [])
    {
        $this->message = \json_encode($message, JSON_UNESCAPED_UNICODE);
    }

    public function setRoute(RouteInterface $route)
    {
        $this->exchange = $route->getRoute();
    }

    public function addConfig(array $params = [])
    {
    }
}
