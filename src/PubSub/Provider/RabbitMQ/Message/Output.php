<?php
/**
 * @package Chocolife.me
 * @author  docxplusgmoon <nurgabylov.d@chocolife.kz>
 */

namespace Chocofamily\PubSub\Provider\RabbitMQ\Message;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Output implements \Chocofamily\PubSub\MessageInterface
{
    /** @var int Кол-во попыток публикации сообщения */
    private $publishAttempts = 5;

    /** @var AMQPMessage */
    private $payload;

    /** @var array */
    private $headers = ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT];

    public function __construct(array $body, array $headers = [], $receiveAttempts = 5)
    {
        $this->headers['message_id'] = $body['event_id'];
        $this->headers = array_merge($this->headers, $headers);

        $this->headers['application_headers']['receive_attempts'] = (int) $receiveAttempts;

        $table = new AMQPTable($this->headers['application_headers']);
        unset($this->headers['application_headers']);

        $this->payload = new AMQPMessage(\json_encode($body), $this->headers);
        $this->payload->set('application_headers', $table);
    }

    public function getHeader($key, $default = null)
    {
        return $this->headers[$key] ?: $default;
    }

    /**
     * @return AMQPMessage
     */
    public function getPayload()
    {
        return $this->payload;
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
        return (bool) --$this->publishAttempts;
    }
}
