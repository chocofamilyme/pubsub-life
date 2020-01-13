<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 14:48
 */

namespace Helper\Unit;

use Chocofamily\PubSub\ReceiveMessageInterface;
use Chocofamily\PubSub\Provider\AbstractProvider;
use Chocofamily\PubSub\RouteInterface;
use Chocofamily\PubSub\SendMessageInterface;

class DummyProvider extends AbstractProvider
{
    public static $channels = [];

    /** @var RouteInterface */
    protected $route;

    public static $connected = false;

    public function connect()
    {
        self::$connected = true;
    }

    public function disconnect()
    {
        self::$connected = false;
    }

    public function publish(SendMessageInterface $message)
    {
        self::$channels[$this->route->getExchange()][$this->route->getRoutes()[0]][] = $message->getPayload();
    }

    public function subscribe($queueName, callable $callback, $consumerTag)
    {
        while (!empty(self::$channels[$this->route->getExchange()])) {
            foreach ($this->route->getRoutes() as $route) {
                if (!isset(self::$channels[$this->route->getExchange()][$route])) {
                    continue;
                }

                foreach (self::$channels[$this->route->getExchange()][$route] as $key => $message) {
                    $input = new DummyReceiveMessage($message);
                    call_user_func($callback, $input->getHeaders(), $input->getBody(), $input->getParams());
                }

                unset(self::$channels[$this->route->getExchange()][$route]);
            }
        }
    }

    public function setRoute(RouteInterface $route)
    {
        $this->route = $route;
    }

    public function getMessage(array $data, array $params)
    {
        $message          = new DummySendMessage();
        $message->data    = $data;
        $message->headers = $params['application_headers'];
        $message->params  = $params;

        return $message;
    }
}
