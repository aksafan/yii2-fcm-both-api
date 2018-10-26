<?php

namespace aksafan\fcm\responses;

use aksafan\fcm\builders\StaticBuilderFactory;
use aksafan\fcm\requests\AbstractRequest;
use aksafan\fcm\requests\Request;
use aksafan\fcm\responses\apiV1\TokenResponse;
use aksafan\fcm\responses\legacyApi\GroupManagementResponse;
use aksafan\fcm\responses\legacyApi\TokenResponse as LegacyTokenResponse;
use aksafan\fcm\responses\legacyApi\TopicResponse as LegacyTopicResponse;
use aksafan\fcm\responses\legacyApi\GroupResponse as LegacyGroupResponse;

/**
 * Class StaticResponseFactory.
 */
final class StaticResponseFactory
{
    const LEGACY_API = 'legacy_api';
    const API_V1 = 'api_v1';
    const AVAILABLE_API_VERSIONS = [
        self::LEGACY_API,
        self::API_V1,
    ];

    /**
     * @param string $apiVersion
     *
     * @param AbstractRequest|Request $request
     *
     * @return AbstractResponse
     *
     * @throws \InvalidArgumentException
     */
    public static function build(string $apiVersion, Request $request): AbstractResponse
    {
        if (StaticBuilderFactory::FOR_TOPIC_MANAGEMENT === $request->getReason()) {
            return new TopicSubscribeResponse();
        }

        if (static::LEGACY_API === $apiVersion) {
            if (StaticBuilderFactory::FOR_TOKEN_SENDING === $request->getReason()) {
                return new LegacyTokenResponse();
            }
            if (StaticBuilderFactory::FOR_TOPIC_SENDING === $request->getReason()) {
                return new LegacyTopicResponse();
            }
            if (StaticBuilderFactory::FOR_GROUP_SENDING === $request->getReason()) {
                return new LegacyGroupResponse();
            }
            if (StaticBuilderFactory::FOR_GROUP_MANAGEMENT === $request->getReason()) {
                return new GroupManagementResponse();
            }
        }

        if (static::API_V1 === $apiVersion && StaticBuilderFactory::FOR_TOKEN_SENDING === $request->getReason()) {
            return new TokenResponse();
        }

        throw new \InvalidArgumentException('api param must be in ['.implode(', ', static::AVAILABLE_API_VERSIONS).'].');
    }
}
