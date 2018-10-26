<?php

namespace aksafan\fcm\responses;

/**
 * Interface TopicSubscribeResponseInterface.
 */
interface TopicSubscribeResponseInterface
{
    /**
     * Returns tokens that was unsuccessfully (un)subscribe to topic with their corresponded errors.
     *
     * @return array
     */
    public function getTopicTokensWithError(): array;
}
