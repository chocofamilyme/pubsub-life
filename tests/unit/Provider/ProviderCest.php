<?php

namespace Unit\Provider;

use Chocofamily\PubSub\Cache\Memcached;
use Chocofamily\PubSub\Repeater;
use Helper\PubSub\DefaultExtendedProvider;

class ProviderCest
{
    public function tryToCreateInstance(\UnitTester $I)
    {
        $cacheConfig = [
            'servers'  => [
                [
                    'host'   => 'localhost',
                    'port'   => 11211,
                    'weight' => 100,
                ],
            ],
            'prefix'   => 'restapi_cache_',
            'cacheDir' => '../storage/cache',
        ];
        $cache       = new Memcached();

        $testProvider = DefaultExtendedProvider::getInstance([], new Repeater($cache));

        $I->assertEquals(get_class($testProvider), DefaultExtendedProvider::class);
    }
}
