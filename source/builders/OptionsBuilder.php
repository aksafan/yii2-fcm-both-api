<?php

namespace aksafan\fcm\source\builders;

/**
 * Builder for creation of options used by FCM.
 *
 * Interface OptionsBuilder
 *
 * Legacy API
 * @link http://firebase.google.com/docs/cloud-messaging/http-server-ref#downstream-http-messages-json
 *
 * API v1
 * @link https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
 */
interface OptionsBuilder
{
    const TOPICS_PATH = '/topics/';

    /**
     * Builds an instance of MessageOptions.
     *
     * @return array
     */
    public function build(): array;
}
