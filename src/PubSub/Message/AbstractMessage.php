<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 15.01.2020
 * Time: 10:04
 */

namespace Chocofamily\PubSub\Message;

/**
 * Class AbstractMessage
 *
 * @package Chocofamily\PubSub\Message
 */
abstract class AbstractMessage implements MessageInterface
{
    /** @var array */
    protected $params = [];

    /** @var array */
    protected $headers = [];

    /** @var array  */
    protected $body = [];

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function isRepeatable()
    {
        return false;
    }
}
