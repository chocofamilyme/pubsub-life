<?php

use Chocofamily\PubSub\Adapter\RabbitMQ;

function getProvider()
{
    return RabbitMQ::fromConfig([
        'adapter'  => 'RabbitMQ',
        'host'     => 'localhost',
        'port'     => 5674,
        'user'     => 'guest',
        'password' => 'guest',
        'app_id'   => 'service.example.com',
    ]);
}
