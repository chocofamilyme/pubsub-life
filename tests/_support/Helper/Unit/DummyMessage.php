<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 14:54
 */

namespace Helper\Unit;


use Chocofamily\PubSub\MessageInterface;

class DummyMessage implements MessageInterface
{
    public $data = [];
    public $headers = [];

    public function getPayload()
    {
        return $this->data;
    }

    public function getHeader($key, $default = null)
    {
        return $this->headers[$key] ?: $default;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function isRepeatable()
    {
        return false;
    }
}
