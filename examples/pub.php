<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

error_reporting(E_ALL ^ E_NOTICE);

$arguments = $argv;
array_shift($arguments);

$payload = [
    'event_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
    'message' => implode(' ', $arguments) ?: 'Empty message',
];

$client = new \Chocofamily\PubSub\Client(getProvider(), new \Chocofamily\PubSub\Route(['book.reserved']));
$client->publish($payload);

echo "OK\n";
