<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider\RabbitMQ\Message;

use PhpAmqpLib\Message\AMQPMessage;
use Chocofamily\PubSub\MessageInterface;

class Input implements MessageInterface
{
    private $headers;

    private $body;

    public function __construct(AMQPMessage $message)
    {
        $this->headers                = array_merge(
            $message->get_properties(),
            $message->get('application_headers')->getNativeData()
        );
        $this->headers['routing_key'] = $message->delivery_info['routing_key'];

        unset($this->headers['application_headers']);

        $this->body = \json_decode($message->body, true);
    }

    public function getPayload()
    {
        return $this->body;
    }

    public function getHeader($key, $default = null)
    {
        return $this->headers[$key] ?: $default;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
