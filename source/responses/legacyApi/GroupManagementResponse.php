<?php

namespace aksafan\fcm\source\responses\legacyApi;

/**
 * Class GroupManagementResponse.
 */
class GroupManagementResponse extends LegacyAbstractResponse implements GroupManagementResponseInterface
{
    const NOTIFICATION_KEY = 'notification_key';

    /**
     * @var string
     */
    private $notificationKey;

    /**
     * Parses the response from (un)subscribing to(from) topic.
     *
     * @param array $responseBody
     */
    public function parseResponse(array $responseBody)  //TODO check response or rename, need to clarify logic
    {
        if (array_key_exists(self::NOTIFICATION_KEY, $responseBody)) {
            $this->setNotificationKey($responseBody[self::NOTIFICATION_KEY]);
            $this->setResult(true);
        }
    }

    /**
     * Returns notification_key - a unique identifier of the device group.
     *
     * @return string
     */
    public function getNotificationKey()
    {
        return $this->notificationKey;
    }

    /**
     * Sets notification_key - a unique identifier of the device group.
     *
     * @param string $notificationKey
     */
    private function setNotificationKey(string $notificationKey)
    {
        $this->notificationKey = $notificationKey;
    }
}
