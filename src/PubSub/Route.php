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
    /**
     * @var string
     */
    protected $exchange = '';

    /** @var array  */
    protected $routes = [];

    public function __construct(array $routes, $exchange = '')
    {
        if (empty($routes)) {
            throw new \InvalidArgumentException("Empty routes");
        }

        $this->routes   = $routes;
        $this->exchange = $exchange;

        if (empty($exchange)) {
            $this->exchange = explode('.', $routes[0])[0];
        }
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
