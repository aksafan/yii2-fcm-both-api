<?php

namespace aksafan\fcm\source\requests;

/**
 * Interface GroupManagementRequest.
 */
interface GroupManagementRequest
{
    /**
     * Creates device group.
     *
     * @param string $groupName
     * @param array $tokens
     *
     * @return GroupManagementRequest|Request
     */
    public function createGroup(string $groupName, array $tokens): self;

    /**
     * Returns NotificationKey from device group.
     *
     * @param string $groupName
     *
     * @return GroupManagementRequest|Request|LegacyApiRequest
     */
    public function getNotificationKey(string $groupName): GroupManagementRequest;

    /**
     * Adds token(s) to device group.
     *
     * @param string $groupName
     * @param string $notificationKey
     * @param array $tokens
     *
     * @return GroupManagementRequest|Request
     */
    public function addToGroup(string $groupName, string $notificationKey, array $tokens): self;

    /**
     * Removes token(s) from device group.
     *
     * @param string $groupName
     * @param string $notificationKey
     * @param array $tokens
     *
     * @return GroupManagementRequest|Request
     */
    public function removeFromGroup(string $groupName, string $notificationKey, array $tokens): self;
}
