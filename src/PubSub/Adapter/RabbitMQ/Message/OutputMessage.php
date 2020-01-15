<?php
/**
 * @package Chocolife.me
 * @author  docxplusgmoon <nurgabylov.d@chocolife.kz>
 */

namespace Chocofamily\PubSub\Adapter\RabbitMQ\Message;

use Chocofamily\PubSub\OutputMessageInterface;
use Chocofamily\PubSub\Message\AbstractMessage;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class OutputMessage extends AbstractMessage implements OutputMessageInterface
{
    /** @var int Кол-во попыток публикации сообщения */
    protected $publishAttempts = 5;

    /** @var array */
    protected $params = [
        'content_type'  => 'application/json',
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ];

    /**
     * Output constructor.
     *
     * @param array $body
     * @param array $params
     * @param array $headers
     */
    public function __construct(array $body, array $headers, array $params)
    {
        $this->headers = $headers;
        $this->body    = $body;

        $this->params['message_id'] = $body['event_id'];
        $this->params               = array_merge($this->params, $params);

        unset($this->body['event_id']);
    }

    /**
     * @return bool
     */
    public function isRepeatable()
    {
        return (bool) --$this->publishAttempts;
    }

    /**
     * @return mixed|AMQPMessage
     */
    public function getPayload()
    {
        $payload = new AMQPMessage(\json_encode($this->body), $this->params);
        $payload->set('application_headers', new AMQPTable($this->headers));

        return $payload;
    }
}
