<?php

/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub;

use Chocofamily\PubSub\Provider\ProviderInterface;

class Subscriber
{
    /** @var ProviderInterface */
    private $provider;

    /** @var string */
    private $routes;

    /** @var string */
    private $exchangeName;

    /** @var array */
    private $params;

    /** @var string */
    private $consumerTag;

    /** @var callable */
    private $callback;

    /**
     * Subscriber constructor.
     *
     * @param ProviderInterface $provider
     * @param string|array      $routes
     * @param array             $params
     * @param string            $consumerTag
     * @param string            $exchangeName
     */
    public function __construct(
        ProviderInterface $provider,
        $routes,
        array $params = [],
        $consumerTag = '',
        $exchangeName = ''
    ) {
        $this->provider     = $provider;
        $this->routes       = $routes;
        $this->params       = $params;
        $this->consumerTag  = $consumerTag;
        $this->exchangeName = $exchangeName;
    }


    public function subscribe($callback)
    {
        $this->provider->setCurrentExchange($this->routes, $this->exchangeName);

        $this->callback = $callback;

        $this->provider->subscribe([$this, 'callback'], $this->params, $this->consumerTag);
    }


    public function callback(MessageInterface $message)
    {
        call_user_func($this->callback, $message->getHeaders(), $message->getPayload());
    }
}
