<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 18:38
 */

namespace Chocofamily\PubSub;

use Chocofamily\PubSub\Message\MessageInterface;

/**
 * Interface SendMessageInterface
 *
 * @package Chocofamily\PubSub
 */
interface OutputMessageInterface extends MessageInterface
{
    /**
     * @return mixed
     */
    public function getPayload();
}
