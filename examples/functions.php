<?php

use Chocofamily\PubSub\Provider\RabbitMQ;

function getProvider()
{
    $config = [
        'adapter'  => 'RabbitMQ',
        'host'     => 'localhost',
        'port'     => 5674,
        'user'     => 'guest',
        'password' => 'guest',
        'app_id'   => 'service.example.com',
    ];

    return RabbitMQ::fromConfig($config);
}
