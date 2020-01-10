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
     * @var array $config
     */
    protected $config;

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
     * @param array $config
     *
     * @return AbstractProvider
     */
    final public static function fromConfig(array $config)
    {
        return new static($config);
    }
}
