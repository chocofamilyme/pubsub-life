<?php

namespace Chocofamily\PubSub\Adapter;

use Chocofamily\PubSub\RouteInterface;

/**
 * Class AbstractAdapter
 *
 * @package Chocofamily\PubSub\Adapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /** @var array  */
    protected $config = [];

    /** @var RouteInterface */
    protected $route;

    /**
     * AbstractProvider constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();

        register_shutdown_function([$this, 'disconnect']);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this|AdapterInterface
     */
    public function withParameter($key, $value)
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * @param RouteInterface $route
     *
     * @return $this|AdapterInterface
     */
    public function withRoute(RouteInterface $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return AbstractAdapter
     */
    final public static function fromConfig(array $config)
    {
        return new static($config);
    }
}
