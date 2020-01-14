<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 10.01.2020
 * Time: 10:15
 */

namespace Chocofamily\PubSub;

/**
 * Class Route
 *
 * @package Chocofamily\PubSub
 */
class Route implements RouteInterface
{
    /** @var string */
    protected $exchange = '';

    /** @var string */
    protected $queue = '';

    /** @var string */
    protected $consumer = '';

    /** @var array */
    protected $routes = [];

    public function __construct(array $routes, $queue, $exchange, $consumer)
    {
        if (empty($routes)) {
            throw new \InvalidArgumentException("Empty routes");
        }

        $this->routes   = $routes;
        $this->exchange = $exchange;
        $this->queue    = $queue;
        $this->consumer = $consumer;
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getConsumer()
    {
        return $this->consumer;
    }
}
