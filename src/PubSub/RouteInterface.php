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
     * Обработчик события на стороне брокера
     *
     * @return string
     */
    public function getExchange();

    /**
     * Список роутов
     *
     * @return array
     */
    public function getRoutes();

    /**
     * Имя очереди
     *
     * @return string
     */
    public function getQueue();

    /**
     * Уникальное имя подписчика
     *
     * @return string
     */
    public function getConsumer();
}
