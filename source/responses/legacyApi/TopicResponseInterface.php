<?php

namespace aksafan\fcm\source\responses\legacyApi;

/**
 * Interface TopicResponseInterface.
 */
interface TopicResponseInterface
{
    /**
     * Returns the error message from sending push to topic(s).
     *
     * @return string
     */
    public function getErrorMessage();
}
