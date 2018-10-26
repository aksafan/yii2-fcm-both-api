<?php

namespace aksafan\fcm\source\requests;

use InvalidArgumentException;

/**
 * Class StaticRequestFactory.
 */
final class StaticRequestFactory
{
    const LEGACY_API = 'legacy_api';
    const API_V1 = 'api_v1';
    const AVAILABLE_API_VERSIONS = [
        self::LEGACY_API,
        self::API_V1,
    ];

    /**
     * @param string $apiVersion
     * @param array $apiParams
     * @param string $reason
     *
     * @return Request|AbstractRequest
     *
     * @throws \Exception
     */
    public static function build(string $apiVersion, array $apiParams, string $reason): Request
    {
        if (static::LEGACY_API === $apiVersion) {
            if (isset($apiParams['serverKey'], $apiParams['senderId'])) {
                return new LegacyApiRequest($apiParams, $reason);
            }

            throw new InvalidArgumentException('apiParams param must be valid according to chosen '.$apiVersion.' version.');

        }

        if (static::API_V1 === $apiVersion) {
            if (isset($apiParams['privateKeyFile'])) {
                return new ApiV1Request($apiParams, $reason);
            }

            throw new InvalidArgumentException('apiParams param must be valid according to chosen '.$apiVersion.' version.');
        }

        throw new InvalidArgumentException('api param must be in ['.implode(', ', static::AVAILABLE_API_VERSIONS).'].');
    }
}
