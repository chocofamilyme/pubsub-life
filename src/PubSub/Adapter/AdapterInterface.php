<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Adapter;

use Chocofamily\PubSub\RouteInterface;

interface AdapterInterface
{
    public function connect();

    public function disconnect();

    public function publish(array $data, array $headers = [], array $params = []);

    /**
     * Callback-фунцкия должна принимать параметр \Chocofamily\PubSub\InputMessageInterface
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public function subscribe(callable $callback);

    /**
     * @param RouteInterface $route
     *
     * @return self
     */
    public function withRoute(RouteInterface $route);

    /**
     * @param $key
     * @param $value
     *
     * @return self
     */
    public function withParameter($key, $value);
}
