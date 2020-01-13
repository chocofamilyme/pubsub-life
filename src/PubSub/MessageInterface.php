<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub;

interface MessageInterface
{
    /**
     * @return mixed
     */
    public function getPayload();

    /**
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function getHeader($key, $default = null);

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return bool
     */
    public function isRepeatable();
}
