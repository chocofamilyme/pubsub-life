<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 03.02.2020
 * Time: 16:38
 */

namespace Chocofamily\PubSub\Event;

/**
 * Interface EventModelInterface
 *
 * @package Chocofamily\PubSub\Event
 */
interface ModelInterface
{
    /**
     * @return integer
     */
    public function getId();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return string
     */
    public function getEntityType();

    /**
     * @return integer
     */
    public function getEntityId();

    /**
     * @return string
     */
    public function getPublisher();

    /**
     * @return string
     */
    public function getEventName();

    /**
     * @return string
     */
    public function getState();

    /**
     * @param array $data
     */
    public function setData(array $data);

    /**
     * @param array $data
     */
    public function setHeader(array $data);

    /**
     * @param array $data
     */
    public function setParams(array $data);

    /**
     * @param string $entityType
     */
    public function setEntityType($entityType);

    /**
     * @param integer $entityId
     */
    public function setEntityId($entityId);

    /**
     * @param string $publisher
     */
    public function setPublisher($publisher);

    /**
     * @param string $eventName
     */
    public function setEventName($eventName);

    /**
     * @param $state
     */
    public function setState($state);
}
