<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider\RabbitMQ\Message;

use PhpAmqpLib\Message\AMQPMessage;
use Chocofamily\PubSub\ReceiveMessageInterface;

class Input implements ReceiveMessageInterface
{
    /** @var array */
    protected $params = [];

    /** @var array */
    protected $headers = [];

    /** @var mixed */
    protected $body;

    public function __construct(AMQPMessage $message)
    {
        $this->body                  = \json_decode($message->body, true);
        $this->params                = $message->get_properties();
        $this->params['routing_key'] = $message->delivery_info['routing_key'];
        $this->headers               = $message->get('application_headers')->getNativeData();

        unset($this->params['application_headers']);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function isRepeatable()
    {
        if (isset($this->headers['receive_attempts'])) {
            return (int) $this->headers['receive_attempts'] > 1;
        }

        return false;
    }
}
