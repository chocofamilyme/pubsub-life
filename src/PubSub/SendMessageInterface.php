<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 18:38
 */

namespace Chocofamily\PubSub;

/**
 * Interface SendMessageInterface
 *
 * @package Chocofamily\PubSub
 */
interface SendMessageInterface
{
    /**
     * @return mixed
     */
    public function getPayload();

    /**
     * @return bool
     */
    public function isRepeatable();
}
