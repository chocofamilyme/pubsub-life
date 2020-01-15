<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 13.01.2020
 * Time: 14:54
 */

namespace Helper\Unit;

use Chocofamily\PubSub\InputMessageInterface;
use Chocofamily\PubSub\Message\AbstractMessage;

/**
 * Class DummyInputMessage
 *
 * @package Helper\Unit
 */
class DummyInputMessage extends AbstractMessage implements InputMessageInterface
{
    public function __construct(DummyOutputMessage $message)
    {
        $this->body    = $message->getBody();
        $this->headers = $message->getHeaders();
        $this->params  = $message->getParams();
    }
}
