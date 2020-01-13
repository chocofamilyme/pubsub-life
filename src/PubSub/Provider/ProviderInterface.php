<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider;

use Chocofamily\PubSub\MessageInterface;
use Chocofamily\PubSub\RouteInterface;

interface ProviderInterface
{
    public function connect();

    public function disconnect();

    public function publish(MessageInterface $message);

    public function subscribe($queueName, callable $callback, $consumerTag);

    public function setRoute(RouteInterface $route);

    public function addConfig(array $params = []);

    /**
     * @param array $data
     * @param array $headers
     * @param null  $receiveAttempts
     *
     * @return MessageInterface
     */
    public function getMessage(array $data, array $headers, $receiveAttempts = null);
}
