<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 19:16
 */

namespace Helper\Unit;

use Chocofamily\PubSub\SendMessageInterface;

class DummySendMessage implements SendMessageInterface
{
    public $data = [];
    public $headers = [];
    public $params = [];

    public function getPayload()
    {
        return $this;
    }

    public function isRepeatable()
    {
        return false;
    }
}
