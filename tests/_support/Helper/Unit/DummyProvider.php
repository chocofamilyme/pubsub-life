<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 14:48
 */

namespace Helper\Unit;

use Chocofamily\PubSub\Adapter\AbstractAdapter;

class DummyProvider extends AbstractAdapter
{
    public static $channels = [];

    public static $connected = false;

    public function connect()
    {
        self::$connected = true;
    }

    public function disconnect()
    {
        self::$connected = false;
    }

    public function publish(array $data, array $headers = [], array $params = [])
    {
        $message = new DummyOutputMessage($data, $headers, $params);

        self::$channels[$this->route->getExchange()][$this->route->getRoutes()[0]][] = $message->getPayload();
    }

    public function subscribe(callable $callback)
    {
        while (!empty(self::$channels[$this->route->getExchange()])) {
            foreach ($this->route->getRoutes() as $route) {
                if (!isset(self::$channels[$this->route->getExchange()][$route])) {
                    continue;
                }

                foreach (self::$channels[$this->route->getExchange()][$route] as $key => $message) {
                    $input = new DummyInputMessage($message);
                    call_user_func($callback, $input);
                }

                unset(self::$channels[$this->route->getExchange()][$route]);
            }
        }
    }
}
