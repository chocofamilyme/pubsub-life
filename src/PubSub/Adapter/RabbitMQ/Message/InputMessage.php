<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub\Adapter\RabbitMQ\Message;

use Chocofamily\PubSub\Message\AbstractMessage;
use PhpAmqpLib\Message\AMQPMessage;
use Chocofamily\PubSub\InputMessageInterface;

class InputMessage extends AbstractMessage implements InputMessageInterface
{
    public function __construct(AMQPMessage $message)
    {
        $this->body   = \json_decode($message->body, true);
        $this->params = $message->get_properties();

        if (isset($message->delivery_info['routing_key'])) {
            $this->params['routing_key'] = $message->delivery_info['routing_key'];
        }

        $this->headers = $message->get('application_headers')->getNativeData();

        unset($this->params['application_headers']);
    }

    public function isRepeatable()
    {
        if (isset($this->headers['receive_attempts'])) {
            return (int) $this->headers['receive_attempts'] > 0;
        }

        return false;
    }
}
