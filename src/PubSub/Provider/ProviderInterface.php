<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider;

use Chocofamily\PubSub\RouteInterface;

interface ProviderInterface
{
    public function connect();

    public function disconnect();

    public function publish();

    public function subscribe($queueName, callable $callback, $consumerTag);

    public function setMessage(array $data, array $headers = []);

    public function setRoute(RouteInterface $route);

    public function addConfig(array $params = []);
}
