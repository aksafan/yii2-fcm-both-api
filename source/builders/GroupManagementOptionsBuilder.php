<?php

namespace aksafan\fcm\source\builders;

use aksafan\fcm\source\helpers\OptionsHelper;
use InvalidArgumentException;

/**
 * Builder for Group management FCM Legacy API.
 *
 * Class GroupManagementOptionsBuilder
 *
 * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#device-group-management
 */
class GroupManagementOptionsBuilder implements OptionsBuilder
{
    /**
     * The operation to run. Valid values are create, add, and remove.
     *
     * @var string
     */
    private $operation;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var string
     */
    private $notificationKeyName;

    /**
     * @var string
     */
    private $notificationKey;

    /**
     * Sets the operation to run. Valid values are 'create', 'add', and 'remove'.
     *
     * @param string $operation
     *
     * @throws InvalidArgumentException
     */
    public function setOperation(string $operation)
    {
        OptionsHelper::validateGroupOperation($operation);
        $this->operation = $operation;
    }

    /**
     * @param array $tokens
     *
     * @throws InvalidArgumentException
     */
    public function setTokensForGroup(array $tokens)
    {
        OptionsHelper::validateTokensValue($tokens);
        $this->tokens = $tokens;
    }

    /**
     * @param string $notificationKeyName Is a name or identifier (e.g., it can be a username) that is unique to a given group.
     */
    public function setNotificationKeyName(string $notificationKeyName)
    {
        $this->notificationKeyName = $notificationKeyName;
    }

    /**
     * @param string $notificationKey Unique identifier of the device group. This value is returned in the response for a successful create operation, and is required for all subsequent operations on the device group.
     */
    public function setNotificationKey(string $notificationKey)
    {
        $this->notificationKey = $notificationKey;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @return array
     */
    public function getTokensForGroup()
    {
        return $this->tokens;
    }

    /**
     * @return string
     */
    public function getNotificationKeyName()
    {
        return $this->notificationKeyName;
    }

    /**
     * @return string
     */
    public function getNotificationKey()
    {
        return $this->notificationKey;
    }

    /**
     * Builds request body data.
     *
     * @return array
     */
    public function build(): array
    {
        return array_filter([
            'operation' => $this->getOperation(),
            'notification_key_name' => $this->getNotificationKeyName(),
            'notification_key' => $this->getNotificationKey(),
            'registration_ids' => $this->getTokensForGroup(),
        ]);
    }
}
