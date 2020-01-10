<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

error_reporting(E_ALL ^ E_NOTICE);

$payload = [
    'event_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
    'name' => 'docx',
    'age' => 25
];

$client = new \Chocofamily\PubSub\Client(getProvider(), new \Chocofamily\PubSub\Route(['book.reserved']));
$client->publish($payload);

echo "OK\n";
