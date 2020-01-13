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
    private $headers = [];

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
        $message = $this->provider->getMessage($data, $this->headers);
        $this->provider->publish($message);
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        if (isset($headers['application_headers'])) {
            $this->headers['application_headers'] = array_merge(
                $headers['application_headers'],
                $this->headers['application_headers']
            );
        }

        $this->headers = array_merge($headers, $this->headers);
    }
}
