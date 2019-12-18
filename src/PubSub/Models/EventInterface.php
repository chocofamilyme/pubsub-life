<?php
/**
 * Created by IntelliJ IDEA.
 * User: User
 * Date: 11/11/2019
 * Time: 1:30 PM
 */

namespace Chocofamily\PubSub\Models;


interface EventInterface extends ModelInterface
{
    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return array
     */
    public function getPayload(): array;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @return string
     */
    public function getExchange(): string;

    /**
     * @return string
     */
    public function getRoutingKey(): string;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @return int
     */
    public function getModelId(): int;

    /**
     * @param int $model_id
     */
    public function setModelId(int $model_id);

    /**
     * @return string
     */
    public function getModelType(): string;
}