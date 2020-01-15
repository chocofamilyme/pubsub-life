<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

$arguments = $argv;
array_shift($arguments);

$payload = [
    'event_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
    'message'  => implode(' ', $arguments) ?: 'Empty message',
];

$route = new \Chocofamily\PubSub\Route(['book.reserved'], '', 'book', '');

getProvider()
    ->withRoute($route)
    ->publish($payload);

echo "OK\n";
