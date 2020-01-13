<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 10.01.2020
 * Time: 9:50
 */

namespace Chocofamily\PubSub;

use Chocofamily\PubSub\Provider\ProviderInterface;

/**
 * Class Client
 *
 * @package Chocofamily\PubSub
 */
class Client
{
    /** @var ProviderInterface */
    private $provider;

    /** @var array */
    private $params = [];

    public function __construct(ProviderInterface $provider, RouteInterface $route, array $params = [])
    {
        $this->provider = $provider;
        $this->provider->setRoute($route);
        $this->provider->addConfig($params);
    }

    /**
     * @param          $queueName
     * @param callable $callback
     * @param string   $consumerTag
     */
    public function subscribe($queueName, callable $callback, $consumerTag)
    {
        $this->provider->subscribe($queueName, $callback, $consumerTag);
    }

    /**
     * @param array $data
     */
    public function publish(array $data)
    {
        if (!isset($this->params['application_headers']['receive_attempts'])) {
            $this->setApplicationHeader('receive_attempts', 5);
        }

        $message = $this->provider->getMessage($data, $this->params);
        $this->provider->publish($message);
    }

    /**
     * @param $key
     * @param $value
     */
    public function setParameter($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setApplicationHeader($key, $value)
    {
        $this->params['application_headers'][$key] = $value;
    }
}
