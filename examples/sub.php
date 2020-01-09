<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

use Chocofamily\PubSub\Subscriber;

error_reporting(E_WARNING);

$params = [
    'queue_name' => 'book',
];

$taskName = 'your_task_name';

$subscriber = new Subscriber(getProvider(), 'book.reserved', $params, $taskName);

$subscriber->subscribe(function ($headers, $body) {
    print_r($headers);
    print_r($body);
});
