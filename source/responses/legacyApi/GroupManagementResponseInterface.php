<?php

namespace aksafan\fcm\source\responses\legacyApi;

/**
 * Interface GroupManagementResponseInterface.
 */
interface GroupManagementResponseInterface
{
    /**
     * Returns notification_key - a unique identifier of the device group.
     *
     * @return string
     */
    public function getNotificationKey();
}
