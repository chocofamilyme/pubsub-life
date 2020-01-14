<?php
/**
 * @package Chocolife.me
 * @author  docxplusgmoon <nurgabylov.d@chocolife.kz>
 */

namespace Chocofamily\PubSub\Adapter\RabbitMQ\Message;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Output implements \Chocofamily\PubSub\OutputMessageInterface
{
    /** @var int Кол-во попыток публикации сообщения */
    private $publishAttempts = 5;

    /** @var array */
    private $payload;

    /** @var array */
    private $params = [
        'content_type'  => 'application/json',
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ];

    /**
     * Output constructor.
     *
     * @param array $body
     * @param array $params Все параметры: \PhpAmqpLib\Message\AMQPMessage::$propertyDefinitions
     */
    public function __construct(array $body, array $params)
    {
        $this->params['message_id'] = $body['event_id'];
        $this->params               = array_merge($this->params, $params);

        $table = new AMQPTable($this->params['application_headers']);
        unset($this->params['application_headers']);

        $this->payload = new AMQPMessage(\json_encode($body), $this->params);
        $this->payload->set('application_headers', $table);
    }

    /**
     * @return bool
     */
    public function isRepeatable()
    {
        return (bool) --$this->publishAttempts;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
