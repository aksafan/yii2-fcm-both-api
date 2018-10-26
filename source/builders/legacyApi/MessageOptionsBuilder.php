<?php

namespace aksafan\fcm\builders\legacyApi;

use aksafan\fcm\builders\OptionsBuilder;
use aksafan\fcm\source\helpers\OptionsHelper;
use InvalidArgumentException;

/**
 * Builder for Message to send by FCM Legacy API.
 *
 * Class MessageOptionsBuilder
 *
 * Official google documentation:
 *
 * @link http://firebase.google.com/docs/cloud-messaging/http-server-ref#downstream-http-messages-json
 */
class MessageOptionsBuilder implements OptionsBuilder
{
    const TOKEN = 'token';
    const TOKENS = 'tokens';
    const TOPIC = 'topic';
    const TOPIC_CONDITION = 'topic_condition';
    const GROUP = 'group';

    const TO_BODY_PARAM = 'to';
    const CONDITION_BODY_PARAM = 'condition';
    const REGISTRATION_IDS_BODY_PARAM = 'registration_ids';

    const TYPES = [
        self::TOKEN,
        self::TOKENS,
        self::TOPIC,
        self::TOPIC_CONDITION,
        self::GROUP,
    ];

    const TO_TYPES = [
        self::TOKEN,
        self::TOPIC,
        self::GROUP,
    ];

    /**
     * @var string|null
     */
    private $collapseKey;

    /**
     * @var string|null
     */
    private $priority;

    /**
     * @var bool
     */
    private $contentAvailable = false;

    /**
     * @var bool
     */
    private $mutableContent = false;

    /**
     * @var string|null
     */
    private $timeToLive;

    /**
     * @var string|null
     */
    private $restrictedPackageName;

    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var array|null
     */
    private $data;

    /**
     * @var array|null
     */
    private $notification;

    /**
     * @var array|null
     */
    private $androidConfig;

    /**
     * @var array|null
     */
    private $apnsConfig;

    /**
     * @var array|null
     */
    private $webPushConfig;

    /**
     * @var string
     */
    private static $target;

    /**
     * @var string|array
     */
    private static $targetValue;

    /**
     * @param string $target One of "condition", "token", "topic" (see constants of current class)
     * @param string|array $value
     *
     * @throws InvalidArgumentException
     */
    public function setTarget(string $target, $value)
    {
        OptionsHelper::validateLegacyApiTarget($target, $value);
        self::$target = $target;
        self::$targetValue = self::TOPIC === $target ? self::TOPICS_PATH.$value : $value;
    }

    /**
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    public function setData(array $data)
    {
        OptionsHelper::validateData($data);
        $this->data = $data;
    }

    /**
     * @param string $title
     * @param string $body
     */
    public function setNotification(string $title, string $body)
    {
        $this->notification = [
            'title' => $title,
            'body' => $body,
        ];
    }

    /**
     * This parameter identifies a group of messages (e.g., with collapse_key: "Updates Available") that can be collapsed, so that only the last message gets sent when delivery can be resumed.
     * A maximum of 4 different collapse keys is allowed at any given time.
     *
     * @param string $collapseKey
     */
    public function setCollapseKey(string $collapseKey)
    {
        $this->collapseKey = $collapseKey;
    }

    /**
     * Sets the priority of the message. Valid values are "normal" and "high."
     * By default, messages are sent with normal priority.
     *
     * @param string $priority
     *
     * @throws InvalidArgumentException
     */
    public function setPriority(string $priority)
    {
        OptionsHelper::validatePriority($priority);
        $this->priority = $priority;
    }

    /**
     * Supports only Android and Ios.
     *
     * An inactive client app is awoken.
     * On iOS, use this field to represent content-available in the APNS payload.
     * On Android, data messages wake the app by default.
     * On Chrome, currently not supported.
     *
     * @param bool $contentAvailable
     */
    public function setContentAvailable(bool $contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;
    }

    /**
     * Supports iOS 10+
     *
     * When a notification is sent and this is set to true,
     * the content of the notification can be modified before it is displayed.
     *
     * @param bool $isMutableContent
     */
    public function setMutableContent(bool $isMutableContent)
    {
        $this->mutableContent = $isMutableContent;
    }

    /**
     * This parameter specifies how long the message should be kept in FCM storage if the device is offline.
     *
     * @param int $timeToLive (in second) min:0 max:2419200
     *
     * @throws InvalidArgumentException
     */
    public function setTimeToLive(int $timeToLive)
    {
        if ($timeToLive < 0 || $timeToLive > 2419200) {
            throw new InvalidArgumentException("time to live must be between 0 and 2419200, current value is: {$timeToLive}");
        }
        $this->timeToLive = $timeToLive;
    }

    /**
     * This parameter specifies the package name of the application where the registration tokens must match in order to receive the message.
     *
     * @param string $restrictedPackageName
     */
    public function setRestrictedPackageName(string $restrictedPackageName)
    {
        $this->restrictedPackageName = $restrictedPackageName;
    }

    /**
     * This parameter, when set to true, allows developers to test a request without actually sending a message.
     * It should only be used for the development.
     *
     * @param bool $isDryRun
     */
    public function setDryRun(bool $isDryRun)
    {
        $this->dryRun = $isDryRun;
    }

    /**
     * @param array $config
     */
    public function setAndroidConfig(array $config)
    {
        OptionsHelper::validateLegacyApiPlatformConfig($config, OptionsHelper::ANDROID);
        $this->androidConfig = $config;
    }

    /**
     * @param array $config
     */
    public function setApnsConfig(array $config)
    {
        OptionsHelper::validateLegacyApiPlatformConfig($config, OptionsHelper::IOS);
        $this->apnsConfig = $config;
    }

    /**
     * @param array $config
     */
    public function setWebPushConfig(array $config)
    {
        OptionsHelper::validateLegacyApiPlatformConfig($config, OptionsHelper::WEB_PUSH);
        $this->webPushConfig = $config;
    }

    /**
     * @return array
     */
    public static function getTokens(): array
    {
        if (self::TOKENS === self::getTarget()) {
            return \is_array(self::getTargetValue()) ? self::getTargetValue() : [];
        }
        if (self::TOKEN === self::getTarget()) {
            return [self::getTargetValue()];
        }

        return [];
    }

    /**
     * @return string
     */
    public static function getTarget(): string
    {
        return (string) self::$target;
    }

    /**
     * @return array|string
     */
    public static function getTargetValue()
    {
        return self::$targetValue;
    }

    /**
     * Builds request body data.
     *
     * @return array
     */
    public function build(): array
    {
        return array_filter([
            $this->getTargetBodyParam() => self::getTargetValue(),
            'notification' => $this->getNotificationOptions(),
            'data' => $this->getData(),
            'priority' => $this->getPriority(),
            'collapse_key' => $this->getCollapseKey(),
            'content_available' => $this->isContentAvailable(),
            'mutable_content' => $this->isMutableContent(),
            'time_to_live' => $this->getTimeToLive(),
            'restricted_package_name' => $this->getRestrictedPackageName(),
            'dry_run' => $this->isDryRun(),
        ]);
    }

    /**
     * @return array
     */
    private function getData()
    {
        return $this->data;
    }

    /**
     * Gets the collapseKey.
     *
     * @return null|string
     */
    private function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * Gets the priority.
     *
     * @return null|string
     */
    private function getPriority()
    {
        return $this->priority;
    }

    /**
     * Is content available.
     *
     * @return bool
     */
    private function isContentAvailable(): bool
    {
        return $this->contentAvailable;
    }

    /**
     * Is mutable content
     *
     * @return bool
     */
    private function isMutableContent(): bool
    {
        return $this->mutableContent;
    }

    /**
     * Gets time to live.
     *
     * @return null|int
     */
    private function getTimeToLive()
    {
        return $this->timeToLive;
    }

    /**
     * Gets restricted package name.
     *
     * @return null|string
     */
    private function getRestrictedPackageName()
    {
        return $this->restrictedPackageName;
    }

    /**
     * Is dry run.
     *
     * @return bool
     */
    private function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * Returns Notification options or Android|Apns|Web push notification options. The first one is not empty.
     * Empty array will be returned if none is not empty.
     *
     * @return array
     */
    private function getNotificationOptions(): array
    {
        $options = [];
        if (! empty($this->notification)) {
            $options = array_merge($options, $this->notification);
        }
        if (! empty($this->androidConfig)) {
            $options = array_merge($options, $this->androidConfig);
        }
        if (! empty($this->apnsConfig)) {
            $options = array_merge($options, $this->apnsConfig);
        }
        if (! empty($this->webPushConfig)) {
            $options = array_merge($options, $this->webPushConfig);
        }

        return $options;
    }

    /**
     * Returns appropriate target body param.
     *
     * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#downstream-http-messages-json
     *
     * @return string
     */
    private function getTargetBodyParam(): string
    {
        $target = self::getTarget();

        if (\in_array($target, self::TO_TYPES, true)) {
            return self::TO_BODY_PARAM;
        }
        if (self::TOPIC_CONDITION === $target) {
            return self::CONDITION_BODY_PARAM;
        }
        if (self::TOKENS === $target) {
            return self::REGISTRATION_IDS_BODY_PARAM;
        }

        return self::TO_BODY_PARAM;
    }
}
