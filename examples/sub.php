<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

error_reporting(E_ALL ^ E_NOTICE);

$params = [
    'queue_name' => 'book',
];

$taskName = 'your_task_name';

$client = new \Chocofamily\PubSub\Client(getProvider(), new \Chocofamily\PubSub\Route(['book.reserved']));
$client->subscribe('book', function (\Chocofamily\PubSub\Provider\RabbitMQ\Message\Input $input) {
    print_r($input->getHeaders());
    print_r($input->getPayload());
}, $taskName);
