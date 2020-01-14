<?php
/**
 * @package Chocolife.me
 * @author  Moldabayev Vadim <moldabayev.v@chocolife.kz>
 */

namespace Chocofamily\PubSub;

interface InputMessageInterface
{
    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return bool
     */
    public function isRepeatable();
}
