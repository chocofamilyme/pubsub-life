<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

$route = new \Chocofamily\PubSub\Route(['book.reserved'], 'book', 'book', 'your_task_name');

getProvider()
    ->withRoute($route)
    ->subscribe(function (\Chocofamily\PubSub\InputMessageInterface $message) {
        print_r([
            'body'    => $message->getBody(),
            'headers' => $message->getHeaders(),
            'params'  => $message->getParams(),
        ]);
    });
