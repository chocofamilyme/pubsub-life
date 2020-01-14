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
    const DEFAULT_REPEAT_ATTEMPTS = 5;

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
    public function resend(InputMessageInterface $message)
    {
        if (!$message->isRepeatable()) {
            return;
        }

        $headers = $message->getHeaders();

        $headers['receive_attempts'] -= 1;

        $this->provider->publish($message->getBody(), $headers, $message->getParams());
    }

    public function inject(&$headers)
    {
        if (!isset($headers['receive_attempts'])) {
            $headers['receive_attempts'] = self::DEFAULT_REPEAT_ATTEMPTS;
        }
    }
}
