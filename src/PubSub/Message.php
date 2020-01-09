<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub;

interface Message
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
}
