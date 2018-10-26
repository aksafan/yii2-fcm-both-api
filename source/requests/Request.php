<?php

namespace aksafan\fcm\requests;

use aksafan\fcm\responses\AbstractResponse;

/**
 * Interface Request.
 */
interface Request
{
    /**
     * Sends POST request
     *
     * @return AbstractResponse
     */
    public function send(): AbstractResponse;

    /**
     * Builds the headers for the request.
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Builds request url.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Sets target (token|topic|condition) and its value.
     *
     * @param string $target
     * @param string|array $value
     *
     * @return Request|LegacyApiRequest|ApiV1Request
     */
    public function setTarget(string $target, $value): self;

    /**
     * Sets List of the predefined keys available for building notification messages for iOS and Android.
     *
     * @param string $title
     * @param string $body
     *
     * @return Request|LegacyApiRequest|ApiV1Request
     */
    public function setNotification(string $title, string $body): self;

    /**
     * Sets data message info.
     *
     * @param array $data
     *
     * @return Request|LegacyApiRequest|ApiV1Request
     */
    public function setData(array $data): self;

    /**
     * @param array $config
     *
     * @return Request|LegacyApiRequest|ApiV1Request
     */
    public function setAndroidConfig(array $config): self;

    /**
     * @param array $config
     *
     * @return Request|LegacyApiRequest|ApiV1Request
     */
    public function setApnsConfig(array $config): self;

    /**
     * @param array $config
     *
     * @return Request|LegacyApiRequest|ApiV1Request
     */
    public function setWebPushConfig(array $config): self;

    /**
     * Builds request options.
     *
     * @return array
     */
    public function getRequestOptions(): array;
}
