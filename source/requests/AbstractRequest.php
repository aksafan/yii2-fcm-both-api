<?php

namespace aksafan\fcm\source\requests;

use aksafan\fcm\source\responses\AbstractResponse;
use GuzzleHttp\Client;

/**
 * Class AbstractRequest.
 */
abstract class AbstractRequest
{
    const POST = 'POST';
    const GET = 'GET';
    const TOPIC_ADD_SUBSCRIPTION_URL = 'https://iid.googleapis.com/iid/v1:batchAdd';
    const TOPIC_REMOVE_SUBSCRIPTION_URL = 'https://iid.googleapis.com/iid/v1:batchRemove';

    /**
     * @var $httpClient Client
     */
    private $httpClient;

    /**
     * @var $response AbstractResponse
     */
    private $response;

    /**
     * @var $reason string
     */
    private $reason;

    /**
     * Gets Http client.
     *
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * Sets Http client.
     *
     * @param Client $httpClient
     */
    public function setHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Sets ResponseInterface.
     *
     * @param $response
     */
    public function setResponse(AbstractResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Gets ResponseInterface.
     *
     * @return AbstractResponse
     */
    public function getResponse(): AbstractResponse
    {
        return $this->response;
    }

    /**
     * Gets Request reason: sending message or (un)subscribing to(from) topic.
     *
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Sets Request reason.
     *
     * @param string $reason
     */
    public function setReason(string $reason)
    {
        $this->reason = $reason;
    }

    /**
     * @param string $topic
     * @param array $tokens
     *
     * @return self|Request
     */
    public function subscribeToTopic(string $topic, array $tokens): AbstractRequest
    {
        $this->getOptionBuilder()->setTopic($topic);
        $this->getOptionBuilder()->setTokensForTopic($tokens);
        $this->getOptionBuilder()->setSubscribeToTopic(true);

        return $this;
    }

    /**
     * @param string $topic
     * @param array $tokens
     *
     * @return self|Request
     */
    public function unsubscribeFromTopic(string $topic, array $tokens): AbstractRequest
    {
        $this->getOptionBuilder()->setTopic($topic);
        $this->getOptionBuilder()->setTokensForTopic($tokens);
        $this->getOptionBuilder()->setSubscribeToTopic(false);

        return $this;
    }
}
