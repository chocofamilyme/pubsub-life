<?php

namespace Chocofamily\PubSub\Provider;

/**
 * Class AbstractProvider
 *
 * @package Chocofamily\PubSub\Provider
 * @author  Kulumbayev Kairzhan <kulumbayev.k@chocolife.kz>
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * AbstractProvider constructor.
     *
     * @param array $config
     */
    final public function __construct(array $config)
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
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    protected function getConfig($key, $default = null)
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
     * @param array $config
     *
     * @return AbstractProvider
     */
    final public static function fromConfig(array $config)
    {
        return new static($config);
    }
}
