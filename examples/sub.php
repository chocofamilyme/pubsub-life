<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

error_reporting(E_ALL ^ E_NOTICE);

$params = [
    'queue_name' => 'book',
];

$taskName = 'your_task_name';

$client = new \Chocofamily\PubSub\Client(getProvider(), new \Chocofamily\PubSub\Route(['book.reserved']));
$client->subscribe('book', function ($headers, $body) {
    print_r($headers);
    print_r($body);
    // throw new \Chocofamily\PubSub\Exceptions\RetryException('RETRY');
}, $taskName);
