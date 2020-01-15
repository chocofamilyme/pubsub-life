<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 15.01.2020
 * Time: 9:36
 */

namespace Chocofamily\PubSub\Message;

/**
 * Interface MessageInterface
 *
 * @package Chocofamily\PubSub
 */
interface MessageInterface
{
    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return bool
     */
    public function isRepeatable();
}
