<?php
/**
 * @package Chocolife.me
 * @author  docxplusgmoon <nurgabylov.d@chocolife.kz>
 */

namespace Chocofamily\PubSub;

interface RepeaterInterface
{
    /**
     * @param MessageInterface $inputMessage
     *
     * @return bool
     */
    public function isRepeatable(MessageInterface $inputMessage);
}
