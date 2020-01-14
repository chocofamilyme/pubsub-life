<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 16:20
 */

class RouteCest
{
    public function tryToTestEmptyRoute(\UnitTester $I)
    {
        $I->expectThrowable(\InvalidArgumentException::class, function () {
            new \Chocofamily\PubSub\Route([], '', '', '');
        });
    }
}
