<?php

namespace aksafan\fcm\source\responses;

use aksafan\fcm\source\helpers\ErrorsHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractResponse.
 */
abstract class AbstractResponse
{
    const ERROR = 'error';
    const UNAUTHORIZED_REQUEST_EXCEPTION_MESSAGE = 'FCM_SENDER_ID or FCM_SERVER_KEY are invalid';

    /**
     * @var string
     */
    private $errorStatusDescription;

    /**
     * @var bool
     */
    private $result = false;

    /**
     * @var
     */
    private $messageId;

    /**
     * Handles response.
     *
     * @param ResponseInterface|null $responseObject
     *
     * @return AbstractResponse
     */
    public function handleResponse($responseObject = null): AbstractResponse
    {
        if ($this->validateResponse($responseObject)) {
            $this->parseResponse($this->getResponseBody($responseObject));
        }

        return $this;
    }

    /**
     * Returns FCM Legacy API error code with id description.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode
     * @link https://firebase.google.com/docs/cloud-messaging/http-server-ref#table9
     *
     * @return string
     */
    public function getErrorStatusDescription()
    {
        return $this->errorStatusDescription;
    }

    /**
     * Result of result of request.
     *
     * @return bool
     */
    public function isResultOk(): bool
    {
        return $this->result;
    }

    /**
     * Returns messageId - the identifier of the message sent.
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Returns response body as a array.
     *
     * @param ResponseInterface $responseObject
     *
     * @return array
     *
     * @throws \ErrorException In order that response from FCM is not valid and parseable.
     */
    protected function getResponseBody(ResponseInterface $responseObject): array
    {
        $result = json_decode((string) $responseObject->getBody(), true);
        if (\is_array($result)) {
            return $result;
        }
        \Yii::error('Response from FCM is not valid. Response code = '.$responseObject->getStatusCode().'. Response info = '.(string) $responseObject->getBody()->getContents(), ErrorsHelper::INVALID_FCM_RESPONSE);

        throw new \ErrorException('Response from FCM is not valid. Look through logs for more info.');
    }

    /**
     * Sets messageId - the identifier of the message sent.
     *
     * @param string $messageId
     */
    protected function setMessageId(string $messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Sets errorStatusDescription property according to the given error status.
     * For appropriate error statuses check constants of 'ErrorsHelper' class.
     *
     * @param string $errorStatus
     * @param string $additionalInfo
     */
    protected function setErrorStatusDescription(string $errorStatus, string $additionalInfo = '')
    {
        $this->errorStatusDescription = ErrorsHelper::getFcmErrorMessage($errorStatus, $additionalInfo);
    }

    /**
     * Sets result of request.
     *
     * @param bool $result
     */
    protected function setResult(bool $result)
    {
        $this->result = $result;
    }

    /**
     * Check if the response given by fcm is parsable.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    abstract public function validateResponse($response): bool;

    /**
     * Parses the response from sending message.
     *
     * @param array $responseBody
     */
    abstract public function parseResponse(array $responseBody);
}
