<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

use Chocofamily\PubSub\Publisher;

error_reporting(E_WARNING);

$publisher = new Publisher(getProvider());

$payload = [
    'event_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
    'name' => 'docx',
    'age' => 25
];

$routeKey = 'book.reserved';

$publisher->send($payload, $routeKey);

echo "OK\n";
