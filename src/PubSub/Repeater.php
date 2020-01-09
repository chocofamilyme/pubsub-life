<?php
/**
 * @package Chocolife.me
 * @author  docxplusgmoon <nurgabylov.d@chocolife.kz>
 */

namespace Chocofamily\PubSub;

/**
 * Class Repeater
 *
 * @package Chocofamily\PubSub
 */
class Repeater implements RepeaterInterface
{
    const REDELIVERY_COUNT = 5;
    const CACHE_LIFETIME   = 1800;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param MessageInterface $inputMessage
     *
     * @return bool
     */
    public function isRepeatable(MessageInterface $inputMessage)
    {
        $key = $this->getCacheKey($inputMessage);

        $redeliveryCount = $this->cache->get($key);

        if (empty($redeliveryCount)) {
            $redeliveryCount = 1;
        }

        $redeliveryCount++;

        $this->cache->set($key, $redeliveryCount, self::CACHE_LIFETIME);

        return ($redeliveryCount <= self::REDELIVERY_COUNT);
    }

    /**
     * @param MessageInterface $inputMessage
     *
     * @return string
     */
    public function getCacheKey(MessageInterface $inputMessage)
    {
        return 'ev_'.$inputMessage->getHeader('app_id').'_'.$inputMessage->getHeader('message_id');
    }
}
