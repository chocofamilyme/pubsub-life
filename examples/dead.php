<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once 'functions.php';

error_reporting(E_ALL ^ E_NOTICE);

$route = new \Chocofamily\PubSub\Route(['dlx'], 'dead-messages', 'dlx', 'dead_task');

getProvider()
    ->withRoute($route)
    ->withParameter('exchange_type', 'fanout')
    ->subscribe(function (\Chocofamily\PubSub\InputMessageInterface $message) {
        print_r([
            'body'    => $message->getBody(),
            'headers' => $message->getHeaders(),
            'params'  => $message->getParams(),
        ]);
    });
