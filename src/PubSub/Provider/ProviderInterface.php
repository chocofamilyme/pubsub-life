<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider;

use Chocofamily\PubSub\RouteInterface;
use Chocofamily\PubSub\SendMessageInterface;

interface ProviderInterface
{
    public function connect();

    public function disconnect();

    public function publish(SendMessageInterface $message);

    public function subscribe($queueName, callable $callback, $consumerTag);

    public function setRoute(RouteInterface $route);

    public function addConfig(array $params = []);

    /**
     * @param array $data
     * @param array $params
     *
     * @return SendMessageInterface
     */
    public function getMessage(array $data, array $params);
}
