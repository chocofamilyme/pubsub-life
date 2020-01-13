<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 14:38
 */

class ProviderCest
{
    public function tryToCreateProvider(\UnitTester $I)
    {
        $appId    = uniqid();
        $provider = \Helper\Unit\DummyProvider::fromConfig([
            'app_id' => $appId,
        ]);

        $I->assertTrue($provider instanceof \Chocofamily\PubSub\Provider\ProviderInterface);
        $I->assertTrue($provider instanceof \Chocofamily\PubSub\Provider\AbstractProvider);
        $I->assertTrue($provider instanceof \Helper\Unit\DummyProvider);
        $I->assertTrue(\Helper\Unit\DummyProvider::$connected);

        $reflection = new ReflectionObject($provider);
        $config     = $reflection->getProperty('config');
        $config->setAccessible(true);

        $I->assertEquals($appId, $config->getValue($provider)['app_id']);
    }

    public function tryToPublishMessage(\UnitTester $I)
    {
        $provider = \Helper\Unit\DummyProvider::fromConfig([]);
        $route    = new \Chocofamily\PubSub\Route(['route1'], 'exchange1');

        $client = new \Chocofamily\PubSub\Client($provider, $route);

        $client->setParameter('app_id', 123);
        $client->setApplicationHeader('message_id', 123);
        $client->setApplicationHeader('user', 123);

        foreach (range(1, 3) as $value) {
            $client->setParameter('app_id', 123);
            $client->setApplicationHeader('message_id', $value);

            $client->publish([
                'id'      => $value,
                'message' => 'Hello, world',
            ]);
        }

        $message = \Helper\Unit\DummyProvider::$channels[$route->getExchange()][$route->getRoutes()[0]][0] ?: null;
        $I->assertTrue($message instanceof \Helper\Unit\DummySendMessage);
    }

    public function tryToSubscribeMessage(\UnitTester $I)
    {
        $provider = \Helper\Unit\DummyProvider::fromConfig([]);
        $route    = new \Chocofamily\PubSub\Route(['route1'], 'exchange1');

        $count         = 0;
        $messageExists = false;

        $client = new \Chocofamily\PubSub\Client($provider, $route);
        $client->subscribe('queue', function (array $headers, array $body, array $params) use (
            $I,
            &$messageExists,
            &$count
        ) {
            $count++;
            $messageExists = true;

            $I->assertEquals($params['app_id'], 123);
            $I->assertEquals($headers['message_id'], $count);
            $I->assertEquals($headers['user'], 123);
            $I->assertEquals($body['id'], $count);
            $I->assertEquals($body['message'], 'Hello, world');
        }, __METHOD__);

        $I->assertTrue($messageExists);
        $I->assertEmpty(\Helper\Unit\DummyProvider::$channels[$route->getExchange()]);
        $I->assertEquals($count, 3);
    }
}
