<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

error_reporting(E_ALL ^ E_NOTICE);

$params = [
    'exchange_type' => 'fanout',
];

$taskName = 'dead_task';

$client = new \Chocofamily\PubSub\Client(getProvider(), new \Chocofamily\PubSub\Route(['dlx']), $params);
$client->subscribe('dead-messages', function ($headers, $body) {
    print_r($headers);
    print_r($body);
}, $taskName);
