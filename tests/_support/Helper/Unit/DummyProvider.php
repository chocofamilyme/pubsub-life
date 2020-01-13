<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 14:48
 */

namespace Helper\Unit;


use Chocofamily\PubSub\MessageInterface;
use Chocofamily\PubSub\Provider\AbstractProvider;
use Chocofamily\PubSub\RouteInterface;

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

    public function publish(MessageInterface $message)
    {
        self::$channels[$this->route->getExchange()][$this->route->getRoutes()[0]][] = $message;
    }

    public function subscribe($queueName, callable $callback, $consumerTag)
    {
        while (!empty(self::$channels[$this->route->getExchange()])) {
            foreach ($this->route->getRoutes() as $route) {
                if (!isset(self::$channels[$this->route->getExchange()][$route])) {
                    continue;
                }
                /** @var MessageInterface $message */
                foreach (self::$channels[$this->route->getExchange()][$route] as $key => $message) {
                    call_user_func($callback, $message->getHeaders(), $message->getPayload());
                }

                unset(self::$channels[$this->route->getExchange()][$route]);
            }
        }
    }

    public function setRoute(RouteInterface $route)
    {
        $this->route = $route;
    }

    public function getMessage(array $data, array $headers, $receiveAttempts = null)
    {
        $message          = new DummyMessage();
        $message->data    = $data;
        $message->headers = $headers;

        return $message;
    }
}
