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
        $provider = \Helper\Unit\DummyProvider::fromConfig([]);

        $I->assertTrue($provider instanceof \Chocofamily\PubSub\Adapter\AdapterInterface);
        $I->assertTrue($provider instanceof \Chocofamily\PubSub\Adapter\AbstractAdapter);
        $I->assertTrue($provider instanceof \Helper\Unit\DummyProvider);
        $I->assertTrue(\Helper\Unit\DummyProvider::$connected);
    }

    public function tryToCreateProviderWithParameter(\UnitTester $I)
    {
        $userId = uniqid();
        $appId  = uniqid();

        $provider = \Helper\Unit\DummyProvider::fromConfig([
            'app_id'  => $appId,
            'user_id' => uniqid(),
        ])
            ->withParameter('user_id', $userId);

        $reflection = new ReflectionObject($provider);
        $config     = $reflection->getProperty('config');
        $config->setAccessible(true);

        $I->assertEquals($appId, $config->getValue($provider)['app_id']);
        $I->assertEquals($userId, $config->getValue($provider)['user_id']);
    }

    public function tryToCreateProviderWithRoute(\UnitTester $I)
    {
        $route1 = new \Chocofamily\PubSub\Route(['route1'], '', 'exchange1', '');

        $provider = \Helper\Unit\DummyProvider::fromConfig([])
            ->withRoute($route1);

        $reflection = new ReflectionObject($provider);
        $route      = $reflection->getProperty('route');
        $route->setAccessible(true);

        $I->assertEquals($route1, $route->getValue($provider));
    }

    public function tryToPublishMessage(\UnitTester $I)
    {
        $range = range(1, 3);

        $provider = \Helper\Unit\DummyProvider::fromConfig([]);
        $route    = new \Chocofamily\PubSub\Route(['route1'], '', 'exchange1', '');

        $provider->withRoute($route);

        foreach ($range as $value) {
            $provider->publish([
                'id'      => $value,
                'message' => 'Hello, world',
            ], [
                'user'       => 123,
                'message_id' => $value,
            ], [
                'app_id' => 123,
            ]);
        }

        $channel = \Helper\Unit\DummyProvider::$channels[$route->getExchange()][$route->getRoutes()[0]];

        $message = $channel[0] ?: null;

        $I->assertTrue($message instanceof \Helper\Unit\DummyOutputMessage);
        $I->assertEquals(count($channel), count($range));
    }

    public function tryToSubscribeMessage(\UnitTester $I)
    {
        $provider = \Helper\Unit\DummyProvider::fromConfig([]);
        $route    = new \Chocofamily\PubSub\Route(['route1'], 'queue', 'exchange1', __METHOD__);

        $count         = 0;
        $messageExists = false;

        $provider
            ->withRoute($route)
            ->subscribe(function (\Chocofamily\PubSub\InputMessageInterface $message) use (
                $I,
                &$messageExists,
                &$count
            ) {
                $count++;
                $messageExists = true;

                $params  = $message->getParams();
                $headers = $message->getHeaders();
                $body    = $message->getBody();

                $I->assertEquals($params['app_id'], 123);
                $I->assertEquals($headers['message_id'], $count);
                $I->assertEquals($headers['user'], 123);
                $I->assertEquals($body['id'], $count);
                $I->assertEquals($body['message'], 'Hello, world');
            });

        $I->assertTrue($messageExists);
        $I->assertEmpty(\Helper\Unit\DummyProvider::$channels[$route->getExchange()]);
        $I->assertEquals($count, 3);
    }
}
