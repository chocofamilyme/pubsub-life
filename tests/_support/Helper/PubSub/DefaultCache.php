<?php
/**
 * @package Chocolife.me
 * @author  docxplusgmoon <nurgabylov.d@chocolife.kz>
 */

namespace Helper\PubSub;

use Chocofamily\PubSub\CacheInterface;

class DefaultCache implements CacheInterface
{
    /** @var array */
    private $data = [];

    public function get($key, $lifetime = null)
    {
        if (isset($this->data[$key]) && $this->data[$key]['expire_time'] > time()) {
            return $this->data[$key]['value'];
        } else {
            unset($this->data[$key]);

            return null;
        }
    }

    /**
     * @param null $key
     * @param null $content
     * @param int  $lifetime - seconds
     * @param null $stopBuffer
     */
    public function set($key = null, $content = null, $lifetime = null, $stopBuffer = null)
    {
        $this->data[$key] = [
            'expire_time' => time() + $lifetime,
            'value'       => $content,
        ];
    }
}
