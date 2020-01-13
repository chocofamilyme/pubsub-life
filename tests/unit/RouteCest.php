<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 16:20
 */

class RouteCest
{
    /**
     * @dataProvider routeProvider
     *
     * @param UnitTester           $I
     * @param \Codeception\Example $example
     */
    public function tryToTestRoute(\UnitTester $I, \Codeception\Example $example)
    {
        $route = new \Chocofamily\PubSub\Route($example['routes'], $example['exchange']);
        $I->assertEquals($route->getRoutes(), $example['result']['routes']);
        $I->assertEquals($route->getExchange(), $example['result']['exchange']);
    }

    public function tryToTestEmptyRoute(\UnitTester $I)
    {
        $I->expectThrowable(\InvalidArgumentException::class, function () {
            new \Chocofamily\PubSub\Route([]);
        });
    }

    protected function routeProvider()
    {
        return [
            [
                'routes'   => [
                    'route',
                    'route2',
                ],
                'exchange' => null,
                'result'   => [
                    'routes'   => [
                        'route',
                        'route2',
                    ],
                    'exchange' => 'route',
                ],
            ],
            [
                'routes'   => [
                    'exchange.route',
                    'exchange1.route',
                ],
                'exchange' => null,
                'result'   => [
                    'routes'   => [
                        'exchange.route',
                        'exchange1.route',
                    ],
                    'exchange' => 'exchange',
                ],
            ],
            [
                'routes'   => [
                    'exchange.route',
                ],
                'exchange' => 'exchange1',
                'result'   => [
                    'routes'   => [
                        'exchange.route',
                    ],
                    'exchange' => 'exchange1',
                ],
            ],
        ];
    }
}
