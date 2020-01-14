<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 19:00
 */

namespace Chocofamily\PubSub\Message;

use Chocofamily\PubSub\Adapter\AdapterInterface;
use Chocofamily\PubSub\InputMessageInterface;

/**
 * Class Repeater
 *
 * @package Chocofamily\PubSub\Provider\RabbitMQ
 */
class Repeater
{
    /**
     * @var AdapterInterface
     */
    protected $provider;

    public function __construct(AdapterInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param InputMessageInterface $message
     */
    public function send(InputMessageInterface $message)
    {
        if (!$message->isRepeatable()) {
            return;
        }

        $params  = $message->getParams();
        $headers = $message->getHeaders();

        $headers['receive_attempts']   -= 1;
        $params['application_headers'] = $headers;

        $this->provider->publish($this->provider->getMessage($message->getBody(), $params));
    }
}
