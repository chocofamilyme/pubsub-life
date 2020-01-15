<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 19:16
 */

namespace Helper\Unit;

use Chocofamily\PubSub\Message\AbstractMessage;
use Chocofamily\PubSub\OutputMessageInterface;

/**
 * Class DummyOutputMessage
 *
 * @package Helper\Unit
 */
class DummyOutputMessage extends AbstractMessage implements OutputMessageInterface
{
    public function __construct(array $body, array $headers, array $params)
    {
        $this->body    = $body;
        $this->headers = $headers;
        $this->params  = $params;
    }

    public function getPayload()
    {
        return $this;
    }
}
