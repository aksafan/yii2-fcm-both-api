<?php

namespace aksafan\fcm\responses\apiV1;

use aksafan\fcm\builders\apiV1\MessageOptionsBuilder;
use aksafan\fcm\helpers\ErrorsHelper;

/**
 * Class TokenResponse.
 */
class TokenResponse extends ApiV1AbstractResponse implements TokenResponseInterface
{
    const NAME = 'name';
    const ERROR_STATUS = 'status';
    const ERROR_CODE = 'code';
    const ERROR_MESSAGE = 'message';
    const ERROR_DETAILS = 'details';

    /**
     * @var string
     */
    private $rawMessageId;

    /**
     * @var bool
     */
    private $error = false;

    /**
     * @var string
     */
    private $errorStatus;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var array
     */
    private $errorDetails = [];

    /**
     * @var array
     */
    private $tokensToDelete = [];

    /**
     * Parses the response from sending message.
     *
     * @param array $responseBody
     */
    public function parseResponse(array $responseBody)
    {
        if (array_key_exists(self::ERROR, $responseBody) && \is_array($errors = $responseBody[self::ERROR])) {
            $this->error = true;
            $this->setErrorStatus($errors);
            $this->setErrorCode($errors);
            $this->setErrorMessage($errors);
            $this->setErrorDetails($errors);
            $this->checkToBeDeleted($this->getErrorStatus(), $this->getErrorCodeFromErrorDetails($this->errorDetails));
            $this->setErrorStatusDescription($this->getErrorStatus());
        }

        if (array_key_exists(self::NAME, $responseBody)) {
            $this->setResult(true);
            $this->setRawMessageId((string) $responseBody[self::NAME]);
            $this->setMessageId($this->getMessageIdFromRawResponse((string) $responseBody[self::NAME]));
        }
    }

    /**
     * Returns rawMessageId - the raw message response from FMC.
     *
     * @return string
     */
    public function getRawMessageId()
    {
        return $this->rawMessageId;
    }

    /**
     * Returns if there was an error during sending push notification.
     *
     * @return bool
     */
    public function getError(): bool
    {
        return $this->error;
    }

    /**
     * Returns FCM APIv1 error status.
     *
     * @return string
     */
    public function getErrorStatus()
    {
        return $this->errorStatus;
    }

    /**
     * Returns FCM APIv1 error code.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns FCM APIv1 error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Returns FCM APIv1 error details.
     *
     * @return array
     */
    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    /**
     * Returns token(s) to delete.
     *
     * You should remove all tokens returned by this method from your database
     *
     * @return array
     */
    public function getTokensToDelete(): array
    {
        return $this->tokensToDelete;
    }

    /**
     * Sets FCM APIv1 error status.
     *
     * @param array $errors
     */
    private function setErrorStatus(array $errors)
    {
        if (array_key_exists(self::ERROR_STATUS, $errors)) {
            $this->errorStatus = (string)$errors[self::ERROR_STATUS];
        }
    }

    /**
     * Sets FCM APIv1 error code.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode
     *
     * @param array $errors
     */
    private function setErrorCode(array $errors)
    {
        if (array_key_exists(self::ERROR_CODE, $errors)) {
            $this->errorCode = $errors[self::ERROR_CODE];
        }
    }

    /**
     * Sets FCM APIv1 error message.
     *
     * @param array $errors
     */
    private function setErrorMessage(array $errors)
    {
        if (array_key_exists(self::ERROR_MESSAGE, $errors)) {
            $this->errorMessage = $errors[self::ERROR_MESSAGE];
        }
    }

    /**
     * Returns FCM APIv1 error details.
     *
     * @param array $errors
     */
    private function setErrorDetails(array $errors)
    {
        if (array_key_exists(self::ERROR_DETAILS, $errors)) {
            $this->errorDetails = \is_array($errors[self::ERROR_DETAILS]) ? $errors[self::ERROR_DETAILS] : [];
        }
    }

    /**
     * Sets rawMessageId - the raw message response from FMC.
     *
     * @param string $rawMessageId
     */
    public function setRawMessageId(string $rawMessageId)
    {
        $this->rawMessageId = $rawMessageId;
    }

    /**
     * Checks if current token needs to be deleted.
     *
     * @param string $errorCodeName
     * @param string $errorCodeFromErrorDetails
     */
    private function checkToBeDeleted(string $errorCodeName, string $errorCodeFromErrorDetails)
    {
        if (ErrorsHelper::UNREGISTERED === $errorCodeName || ErrorsHelper::UNREGISTERED === $errorCodeFromErrorDetails) {
            $this->tokensToDelete[] = MessageOptionsBuilder::getToken();
        }
    }

    /**
     * Gets the message id from raw FCM message response.
     *
     * @param string $rawMessage
     *
     * @return string
     */
    private function getMessageIdFromRawResponse(string $rawMessage): string
    {
        $result = substr($rawMessage, strrpos($rawMessage, '/' ) + 1);

        return false !== $result ? $result : 'Message id parse error.';
    }

    /**
     * Returns error code from error details.
     *
     * @param array $errorDetails
     *
     * @return string
     */
    private function getErrorCodeFromErrorDetails(array $errorDetails): string
    {
        $errorCode = '';
        foreach ($errorDetails as $errorDetail) {
            if (array_key_exists('errorCode', $errorDetail)) {
                $errorCode = $errorDetail['errorCode'];
                break;
            }
        }

        return $errorCode;
    }
}
