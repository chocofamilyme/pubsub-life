<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 10.01.2020
 * Time: 10:16
 */

namespace Chocofamily\PubSub;


interface RouteInterface
{
    /**
     * @return string
     */
    public function getExchange();

    /**
     * @return array
     */
    public function getRoutes();
}
