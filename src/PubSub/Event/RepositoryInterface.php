<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 03.02.2020
 * Time: 13:55
 */

namespace Chocofamily\PubSub\Event;

/**
 * Interface RepositoryInterface
 *
 * @package Chocofamily\PubSub\Event
 */
interface RepositoryInterface
{
    /**
     * @param ModelInterface $eventModel
     *
     * @return mixed
     */
    public static function save(ModelInterface $eventModel);

    /**
     * @param $id
     *
     * @return ModelInterface
     */
    public static function findById($id);
}
