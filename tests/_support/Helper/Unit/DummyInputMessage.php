<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 14:54
 */

namespace Helper\Unit;


use Chocofamily\PubSub\InputMessageInterface;

class DummyInputMessage implements InputMessageInterface
{
    private $data    = [];
    private $headers = [];
    private $params  = [];

    public function __construct(DummyOutputMessage $message)
    {
        $this->data    = $message->data;
        $this->headers = $message->headers;
        $this->params  = $message->params;
    }

    public function getBody()
    {
        return $this->data;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function isRepeatable()
    {
        return false;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
