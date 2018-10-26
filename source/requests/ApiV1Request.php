<?php

namespace aksafan\fcm\source\requests;

use aksafan\fcm\source\auth\ServiceAccount;
use aksafan\fcm\source\builders\apiV1\MessageOptionsBuilder;
use aksafan\fcm\source\builders\TopicSubscriptionOptionsBuilder;
use aksafan\fcm\source\builders\OptionsBuilder;
use aksafan\fcm\source\builders\StaticBuilderFactory;
use aksafan\fcm\source\helpers\ErrorsHelper;
use aksafan\fcm\source\responses\AbstractResponse;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Class ApiV1Request.
 */
class ApiV1Request extends AbstractRequest implements Request
{
    const SEND_MESSAGE_URL = 'https://fcm.googleapis.com/v1/projects/';
    const SEND_MESSAGE_URL_PARAMS = '/messages:send';
    const FCM_AUTH_URL = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * @var $serviceAccount ServiceAccount
     */
    private $serviceAccount;

    /**
     * @var $optionBuilder MessageOptionsBuilder|TopicSubscriptionOptionsBuilder
     */
    private $optionBuilder;

    /**
     * Request's constructor.
     *
     * @param array $apiParams
     * @param string $reason
     *
     * @throws \Exception
     */
    public function __construct(array $apiParams, string $reason)
    {
        $this->serviceAccount = new ServiceAccount($apiParams['privateKeyFile']);
        $this->setHttpClient($this->serviceAccount->authorize(self::FCM_AUTH_URL));
        $this->setReason($reason);
        $this->optionBuilder = StaticBuilderFactory::build($reason, $this);
    }

    /**
     * Sets target (token|topic|condition) and its value.
     *
     * @param string $target
     * @param string $value
     *
     * @return Request
     */
    public function setTarget(string $target, $value): Request
    {
        $this->getOptionBuilder()->setTarget($target, (string) $value);

        return $this;
    }

    /**
     * Sets data message info.
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     *
     * @return self
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
     * @param bool $validateOnly Flag for testing the request without actually delivering the message.
     *
     * @return self
     */
    public function validateOnly(bool $validateOnly = true): Request
    {
        $this->getOptionBuilder()->setValidateOnly($validateOnly);

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
     * Builds the headers for the request.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Builds request url.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getUrl(): string
    {
        if (StaticBuilderFactory::FOR_TOPIC_MANAGEMENT === $this->getReason()) {
            return $this->getOptionBuilder()->getSubscriptionStatus() ? self::TOPIC_ADD_SUBSCRIPTION_URL : self::TOPIC_REMOVE_SUBSCRIPTION_URL;
        }

        return self::SEND_MESSAGE_URL.$this->serviceAccount->getProjectId().self::SEND_MESSAGE_URL_PARAMS;
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
     * @return MessageOptionsBuilder|TopicSubscriptionOptionsBuilder
     */
    public function getOptionBuilder()
    {
        return $this->optionBuilder;
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
            'json' => [
                'validate_only' => $this->getOptionBuilder()->getValidateOnly(),
                'message' => $this->getOptionBuilder()->build(),
            ],
        ];
    }

    /**
     * Returns the request options.
     *
     * @return array
     */
    private function getSubscribeTopicOptions(): array
    {
        return [
            'headers' => array_merge($this->getHeaders(), ['access_token_auth' => 'true']),
            'json' => [
                'to' => OptionsBuilder::TOPICS_PATH . $this->getOptionBuilder()->getTopic(),
                'registration_tokens' => $this->getOptionBuilder()->build(),
            ],
        ];
    }
}
