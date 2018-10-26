<?php

namespace aksafan\fcm\source\requests;

use aksafan\fcm\source\builders\GroupManagementOptionsBuilder;
use aksafan\fcm\source\builders\legacyApi\MessageOptionsBuilder;
use aksafan\fcm\source\builders\OptionsBuilder;
use aksafan\fcm\source\builders\StaticBuilderFactory;
use aksafan\fcm\source\builders\TopicSubscriptionOptionsBuilder;
use aksafan\fcm\source\helpers\ErrorsHelper;
use aksafan\fcm\source\responses\AbstractResponse;
use aksafan\fcm\source\helpers\OptionsHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Class LegacyApiRequest.
 */
class LegacyApiRequest extends AbstractRequest implements Request, GroupManagementRequest
{
    const SEND_MESSAGE_URL = 'https://fcm.googleapis.com/fcm/send';
    const MANAGE_GROUP_URL = 'https://fcm.googleapis.com/fcm/notification';
    const NOTIFICATION_KEY_NAME_PARAM = '?notification_key_name=';

    /**
     * @internal
     *
     * @var string
     */
    private $serverKey;

    /**
     * @internal
     *
     * @var string
     */
    private $senderId;

    /**
     * @var $optionBuilder MessageOptionsBuilder|TopicSubscriptionOptionsBuilder
     */
    private $optionBuilder;

    /**
     * Request's constructor.
     *
     * @param array $apiParams
     * @param string $reason
     */
    public function __construct(array $apiParams, string $reason)
    {
        $this->serverKey = $apiParams['serverKey'];
        $this->senderId = $apiParams['senderId'];
        $this->setHttpClient(new Client());
        $this->setReason($reason);
        $this->optionBuilder = StaticBuilderFactory::build($reason, $this);
    }

    /**
     * Sets target (token|tokens|topic|topics|group) and its value.
     *
     * @param string $target
     * @param string|array $value
     *
     * @return LegacyApiRequest
     */
    public function setTarget(string $target, $value): Request
    {
        $this->getOptionBuilder()->setTarget($target, $value);

        return $this;
    }

    /**
     * Sets data message info.
     *
     * @param array $data
     *
     * @return LegacyApiRequest
     */
    public function setData(array $data): Request
    {
        $this->getOptionBuilder()->setData($data);

        return $this;
    }

    /**
     * @param string $title
     * @param string $body
     *
     * @return self
     */
    public function setNotification(string $title, string $body): Request
    {
        $this->getOptionBuilder()->setNotification($title, $body);

        return $this;
    }

    /**
     * @param array $config
     *
     * @return self
     */
    public function setAndroidConfig(array $config): Request
    {
        $this->getOptionBuilder()->setAndroidConfig($config);

        return $this;
    }

    /**
     * @param array $config
     *
     * @return self
     */
    public function setApnsConfig(array $config): Request
    {
        $this->getOptionBuilder()->setApnsConfig($config);

        return $this;
    }

    /**
     * @param array $config
     *
     * @return self
     */
    public function setWebPushConfig(array $config): Request
    {
        $this->getOptionBuilder()->setWebPushConfig($config);

        return $this;
    }

    /**
     * This parameter identifies a group of messages (e.g., with collapse_key: "Updates Available") that can be collapsed, so that only the last message gets sent when delivery can be resumed.
     * A maximum of 4 different collapse keys is allowed at any given time.
     *
     * @param string $collapseKey
     *
     * @return LegacyApiRequest
     */
    public function setCollapseKey(string $collapseKey): Request
    {
        $this->getOptionBuilder()->setCollapseKey($collapseKey);

        return $this;
    }

    /**
     * Sets the priority of the message. Valid values are "normal" and "high."
     * By default, messages are sent with normal priority.
     *
     * @param string $priority
     *
     * @return LegacyApiRequest
     *
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function setPriority(string $priority): Request
    {
        $this->getOptionBuilder()->setPriority($priority);

        return $this;
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
     *
     * @return LegacyApiRequest
     */
    public function setContentAvailable(bool $contentAvailable): Request
    {
        $this->getOptionBuilder()->setContentAvailable($contentAvailable);

        return $this;
    }

    /**
     * Supports iOS 10+
     *
     * When a notification is sent and this is set to true,
     * the content of the notification can be modified before it is displayed.
     *
     * @param bool $isMutableContent
     *
     * @return LegacyApiRequest
     */
    public function setMutableContent(bool $isMutableContent): Request
    {
        $this->getOptionBuilder()->setMutableContent($isMutableContent);

        return $this;
    }

    /**
     * This parameter specifies how long the message should be kept in FCM storage if the device is offline.
     *
     * @param int $timeToLive (in second) min:0 max:2419200
     *
     * @return LegacyApiRequest
     *
     * @throws InvalidArgumentException
     */
    public function setTimeToLive(int $timeToLive): Request
    {
        $this->getOptionBuilder()->setTimeToLive($timeToLive);

        return $this;
    }

    /**
     * This parameter specifies the package name of the application where the registration tokens must match in order to receive the message.
     *
     * @param string $restrictedPackageName
     *
     * @return LegacyApiRequest
     */
    public function setRestrictedPackageName(string $restrictedPackageName): Request
    {
        $this->getOptionBuilder()->setRestrictedPackageName($restrictedPackageName);

        return $this;
    }

    /**
     * This parameter, when set to true, allows developers to test a request without actually sending a message.
     * It should only be used for the development.
     *
     * @param bool $validateOnly
     *
     * @return LegacyApiRequest
     */
    public function validateOnly(bool $validateOnly = true): Request
    {
        $this->getOptionBuilder()->setDryRun($validateOnly);

        return $this;
    }

    /**
     * Creates device group.
     *
     * @param string $groupName
     * @param array $tokens
     *
     * @return GroupManagementRequest|Request
     */
    public function createGroup(string $groupName, array $tokens): GroupManagementRequest
    {
        $this->getOptionBuilder()->setOperation(OptionsHelper::GROUP_CREATE);
        $this->getOptionBuilder()->setNotificationKeyName($groupName);
        $this->getOptionBuilder()->setTokensForGroup($tokens);

        return $this;
    }

    /**
     * Returns NotificationKey from device group.
     *
     * @param string $groupName
     *
     * @return GroupManagementRequest|Request|LegacyApiRequest
     */
    public function getNotificationKey(string $groupName): GroupManagementRequest
    {
        $this->getOptionBuilder()->setNotificationKeyName($groupName);

        return $this;
    }

    /**
     * Adds token(s) to device group.
     *
     * @param string $groupName
     * @param string $notificationKey
     * @param array $tokens
     *
     * @return GroupManagementRequest|Request
     */
    public function addToGroup(string $groupName, string $notificationKey, array $tokens): GroupManagementRequest
    {
        $this->getOptionBuilder()->setOperation(OptionsHelper::GROUP_ADD);
        $this->getOptionBuilder()->setNotificationKeyName($groupName);
        $this->getOptionBuilder()->setNotificationKey($notificationKey);
        $this->getOptionBuilder()->setTokensForGroup($tokens);

        return $this;
    }

    /**
     * Removes token(s) from device group.
     *
     * @param string $groupName
     * @param string $notificationKey
     * @param array $tokens
     *
     * @return GroupManagementRequest|Request
     */
    public function removeFromGroup(string $groupName, string $notificationKey, array $tokens): GroupManagementRequest
    {
        $this->getOptionBuilder()->setOperation(OptionsHelper::GROUP_REMOVE);
        $this->getOptionBuilder()->setNotificationKeyName($groupName);
        $this->getOptionBuilder()->setNotificationKey($notificationKey);
        $this->getOptionBuilder()->setTokensForGroup($tokens);

        return $this;
    }

    /**
     * Sends POST request
     *
     * @return AbstractResponse
     *
     * @throws \Exception
     */
    public function send(): AbstractResponse
    {
        try {
            $responseObject = $this->getHttpClient()->request(self::POST, $this->getUrl(), $this->getRequestOptions());
        } catch (ClientException $e) {
            \Yii::error(ErrorsHelper::getGuzzleClientExceptionMessage($e), ErrorsHelper::GUZZLE_HTTP_CLIENT);
            $responseObject = $e->getResponse();
        } catch (GuzzleException $e) {
            \Yii::error(ErrorsHelper::getGuzzleExceptionMessage($e), ErrorsHelper::GUZZLE_HTTP_CLIENT);
            $responseObject = null;
        }

        return $this->getResponse()->handleResponse($responseObject);
    }

    /**
     * Sends GET request
     *
     * @return AbstractResponse
     *
     * @throws \Exception
     */
    public function sendGET(): AbstractResponse
    {
        try {
            $responseObject = $this
                ->getHttpClient()
                ->request(
                    self::GET,
                    $this->getNotificationKeyUrl($this->getOptionBuilder()->getNotificationKeyName()),
                    ['headers' => $this->getHeaders()]
                );
        } catch (ClientException $e) {
            \Yii::error(ErrorsHelper::getGuzzleClientExceptionMessage($e), ErrorsHelper::GUZZLE_HTTP_CLIENT);
            $responseObject = $e->getResponse();
        } catch (GuzzleException $e) {
            \Yii::error(ErrorsHelper::getGuzzleExceptionMessage($e), ErrorsHelper::GUZZLE_HTTP_CLIENT);
            $responseObject = null;
        }

        return $this->getResponse()->handleResponse($responseObject);
    }

    /**
     * Builds the headers for the request.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Authorization' => 'key='.$this->getServerKey(),
            'Content-Type' => 'application/json',
            'project_id' => $this->getSenderId(),
        ];
    }

    /**
     * Builds request url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        if (StaticBuilderFactory::FOR_TOPIC_MANAGEMENT === $this->getReason()) { //TODO: add topic url
            return $this->optionBuilder->getSubscriptionStatus() ? self::TOPIC_ADD_SUBSCRIPTION_URL : self::TOPIC_REMOVE_SUBSCRIPTION_URL;
        }
        if (StaticBuilderFactory::FOR_GROUP_MANAGEMENT === $this->getReason()) {
            return self::MANAGE_GROUP_URL;
        }

        return self::SEND_MESSAGE_URL;
    }

    /**
     * Builds request options.
     *
     * @return array
     */
    public function getRequestOptions(): array
    {
        if (StaticBuilderFactory::FOR_TOPIC_MANAGEMENT === $this->getReason()) {
            return $this->getSubscribeTopicOptions();
        }

        return $this->getSendMessageOptions();
    }

    /**
     * @return OptionsBuilder|MessageOptionsBuilder|GroupManagementOptionsBuilder|TopicSubscriptionOptionsBuilder
     */
    public function getOptionBuilder()
    {
        return $this->optionBuilder;
    }

    /**
     * Gets serverKey.
     *
     * @return string
     */
    private function getServerKey(): string
    {
        return $this->serverKey;
    }

    /**
     * Gets senderId.
     *
     * @return string
     */
    private function getSenderId(): string
    {
        return $this->senderId;
    }

    /**
     * Returns the request options.
     *
     * @return array
     */
    private function getSendMessageOptions(): array
    {
        return [
            'headers' => $this->getHeaders(),
            'json' => $this->getOptionBuilder()->build(),
        ];
    }

    /**
     * Returns the request options.
     *
     * @return array
     */
    private function getSubscribeTopicOptions(): array
    {
        return
            [
                'headers' => $this->getHeaders(),
                'json' => [
                    'to' => OptionsBuilder::TOPICS_PATH.$this->optionBuilder->getTopic(),
                    'registration_tokens' => $this->optionBuilder->build(),
                ],
            ];
    }

    /**
     * Builds request url for grabbing NotificationKey.
     *
     * @param string $groupName
     *
     * @return string
     */
    private function getNotificationKeyUrl(string $groupName): string
    {
        return self::MANAGE_GROUP_URL.self::NOTIFICATION_KEY_NAME_PARAM.$groupName;
    }
}
