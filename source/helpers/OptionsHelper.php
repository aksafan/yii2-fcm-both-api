<?php

namespace aksafan\fcm\source\helpers;

use aksafan\fcm\source\builders\apiV1\MessageOptionsBuilder;
use aksafan\fcm\source\builders\legacyApi\MessageOptionsBuilder as LegacyMessageOptionsBuilder;
use InvalidArgumentException;

/**
 * Class OptionsHelper.
 */
final class OptionsHelper
{
    /**
     * @const high priority : iOS, these correspond to APNs priorities 10.
     */
    const HIGH = 'high';

    /**
     * @const normal priority : iOS, these correspond to APNs priorities 5
     */
    const NORMAL = 'normal';

    /**
     * @const Possible priority messages' options for Android.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
     */
    const PRIORITY_OPTIONS = [
        self::HIGH,
        self::NORMAL,
    ];

    const GROUP_CREATE = 'create';
    const GROUP_ADD = 'add';
    const GROUP_REMOVE = 'remove';

    /**
     * @const Possible operations to do for device group management. Valid values are create, add, and remove..
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#error-codes
     */
    const GROUP_OPERATIONS = [
        self::GROUP_CREATE,
        self::GROUP_ADD,
        self::GROUP_REMOVE,
    ];

    /**
     * @const Possible notification messages' options for Android.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
     */
    const LEGACY_API_ANDROID_OPTIONS = [
        'title',
        'body',
        'android_channel_id',
        'icon',
        'sound',
        'tag',
        'color',
        'click_action',
        'body_loc_key',
        'body_loc_args',
        'title_loc_key',
        'title_loc_args',
    ];

    /**
     * @const Possible notification messages' options for iOS.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
     */
    const LEGACY_API_IOS_OPTIONS = [
        'title',
        'body',
        'sound',
        'badge',
        'click_action',
        'subtitle',
        'body_loc_key',
        'body_loc_args',
        'title_loc_key',
        'title_loc_args',
    ];

    /**
     * @const Possible notification messages' options for Web push.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
     */
    const LEGACY_API_WEB_PUSH_OPTIONS = [
        'title',
        'body',
        'icon',
        'click_action',
    ];

    const ANDROID = 'android';
    const IOS = 'ios';
    const WEB_PUSH = 'web_push';
    const PLATFORM_OPTIONS = [
        self::ANDROID => self::LEGACY_API_ANDROID_OPTIONS,
        self::IOS => self::LEGACY_API_IOS_OPTIONS,
        self::WEB_PUSH => self::LEGACY_API_WEB_PUSH_OPTIONS,
    ];

    const MAX_TOKENS_PER_REQUEST = 1000;

    /**
     * Validates priority for supporting by FCM.
     *
     * @param string $priority
     *
     * @throws InvalidArgumentException
     */
    public static function validatePriority(string $priority)
    {
        if (! \in_array($priority, self::PRIORITY_OPTIONS, true)) {
            throw new InvalidArgumentException('priority is not valid, please refer to the documentation or use the constants OptionsHelper::PRIORITY_OPTIONS');
        }
    }

    /**
     * Validates operations to do for device group management.
     *
     * @param string $operation
     *
     * @throws InvalidArgumentException
     */
    public static function validateGroupOperation(string $operation)
    {
        if (! \in_array($operation, self::GROUP_OPERATIONS, true)) {
            throw new InvalidArgumentException('operation is not valid, please refer to the documentation or use the constants OptionsHelper::GROUP_OPERATIONS');
        }
    }

    /**
     * Validates FCM APIv1 target and its value
     *
     * Official FCM documentation
     *
     * @link https://firebase.google.com/docs/cloud-messaging/admin/send-messages#send_to_a_condition
     * @link https://firebase.google.com/docs/cloud-messaging/admin/send-messages#send_to_a_topic
     *
     * @param string $target
     * @param string $value
     *
     * @throws InvalidArgumentException
     */
    public static function validateApiV1Target(string $target, string $value)
    {
        switch ($target) {
            case MessageOptionsBuilder::TOPIC_CONDITION:
                self::validateConditionValue($value);
                break;
            case MessageOptionsBuilder::TOPIC:
                self::validateTopicValue($value);
                break;
            case MessageOptionsBuilder::TOKEN:
                break;
            default:
                throw new InvalidArgumentException('Invalid target type "'.$target.'", valid type: "'.implode(', ', MessageOptionsBuilder::TYPES));
        }
    }

    /**
     * Validates FCM Legacy API target and its value
     *
     * @param string $target
     * @param string|array $value
     *
     * @throws InvalidArgumentException
     */
    public static function validateLegacyApiTarget(string $target, $value)
    {
        switch ($target) {
            case LegacyMessageOptionsBuilder::TOPIC_CONDITION:
                self::validateConditionValue($value);
                break;
            case LegacyMessageOptionsBuilder::TOPIC:
                self::validateTopicValue($value);
                break;
            case LegacyMessageOptionsBuilder::TOKEN:
                break;
            case LegacyMessageOptionsBuilder::TOKENS:
                self::validateTokensValue($value);
                break;
            case LegacyMessageOptionsBuilder::GROUP:
                break;
            default:
                throw new InvalidArgumentException('Invalid target type "'.$target.'", valid type: "'.implode(', ', LegacyMessageOptionsBuilder::TYPES));
        }
    }

    /**
     * Validates FCM APIv1 data
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    public static function validateData(array $data)
    {
        foreach ($data as $key => $value) {
            if (! \is_string($key) || ! \is_string($value)) {
                throw new InvalidArgumentException('The keys and values in message data must be all strings.');
            }
        }
    }

    /**
     * Validates notification messages options for Android|IOS|Web push
     *
     * @param array $data
     * @param string $platform
     *
     * @throws InvalidArgumentException
     */
    public static function validateLegacyApiPlatformConfig(array $data, string $platform)
    {
        foreach ($data as $key => $value) {
            if (! \is_string($key) || ! \is_string($value)) {
                throw new InvalidArgumentException('The keys and values in '.$platform.' notification messages options must be all strings.');
            }
            if (! \in_array($key, self::getPlatformOptions($platform), true)) {
                throw new InvalidArgumentException('The keys in '.$platform.' notification messages options must be appropriate according to official documentation. Look for of the class "OptionsHelper" constants');
            }
        }
    }

    /**
     * Validates FCM topic value
     * One can choose any topic name that matches the regular expression: "[a-zA-Z0-9-_.~%]+".
     *
     * @param string $value
     */
    public static function validateTopicValue(string $value)
    {
        $value = trim(preg_replace('@^/topic/@', '', $value), '/');
        if (preg_match('/[^a-zA-Z0-9-_.~]$/', $value)) {
            throw new InvalidArgumentException(sprintf('Malformed topic name "%s".', $value));
        }
    }

    /**
     * Validates tokens value
     *
     * Official FCM documentation
     *
     * @link https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_topics_2
     *
     * @param array $value
     */
    public static function validateTokensValue($value)
    {
        if (! \is_array($value)) {
            throw new InvalidArgumentException('Tokens target value must be an array');
        }
        if (empty($value)) {
            throw new InvalidArgumentException('An empty array of tokens given');
        }
        if (\count($value) > self::MAX_TOKENS_PER_REQUEST) {
            throw new InvalidArgumentException('You can use only 1000 devices in a single request');
        }
    }

    /**
     * Validates FCM APIv1 condition value
     *
     * Official FCM documentation
     *
     * APIv1
     * @link https://firebase.google.com/docs/cloud-messaging/admin/send-messages#send_to_a_condition
     *
     * Legacy API
     * @link https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_topics
     *
     * @param string $value
     */
    private static function validateConditionValue(string $value)
    {
        $value = str_replace('"', "'", $value);
        if ((substr_count($value, "'") % 2) !== 0) {
            throw new InvalidArgumentException(sprintf('The condition "%s" contains an uneven amount of quotes.', $value));
        }
    }

    /**
     * Returns platform options.
     *
     * @param string $platform
     *
     * @return array
     */
    private static function getPlatformOptions(string $platform): array
    {
        if (array_key_exists($platform, self::PLATFORM_OPTIONS)) {
            return self::PLATFORM_OPTIONS[$platform];
        }

        throw new InvalidArgumentException('platform must be appropriate. Look for of the class "OptionsHelper" constants');
    }
}