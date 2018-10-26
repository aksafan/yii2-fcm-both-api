<?php

namespace aksafan\fcm\source\responses\legacyApi;

use aksafan\fcm\source\helpers\ErrorsHelper;
use aksafan\fcm\source\responses\AbstractResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Class LegacyAbstractResponse.
 */
abstract class LegacyAbstractResponse extends AbstractResponse
{
    const MESSAGE_ID = 'message_id';
    const RESULTS = 'results';
    const SUCCESS = 'success';
    const FAILURE = 'failure';

    /**
     * @var int|null
     */
    private $retryAfter;

    /**
     * Check if the response given by fcm is parsable.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function validateResponse($response): bool
    {
        if (null === $response) {
            return false;
        }

        if (200 === ($statusCode = $response->getStatusCode())) {
            return true;
        }

        $responseContents = $response->getBody()->getContents();
        if (400 === $statusCode) {
            \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, $responseContents, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            \Yii::error('Something in the request data was wrong: check if all data{...}values are converted to strings.', ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            $this->setErrorStatusDescription(ErrorsHelper::STATUS_CODE_400, $responseContents);

            return false;
        }

        if (401 === $statusCode) {
            \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, self::UNAUTHORIZED_REQUEST_EXCEPTION_MESSAGE, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            \Yii::error('To use the new FCM HTTP Legacy API, you need to enable FCM API on your Google API dashboard first - https://console.developers.google.com/apis/library/fcm.googleapis.com/.', ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            $this->setErrorStatusDescription(ErrorsHelper::STATUS_CODE_403, $responseContents);
            return false;
        }

        \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, $responseContents, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_OTHER_ERRORS);
        $this->setErrorStatusDescription(ErrorsHelper::OTHER_STATUS_CODES, $responseContents);
        $this->setRetryAfter($response);

        return false;
    }

    /**
     * Returns retryAfter
     *
     * @return int|null
     */
    public function getRetryAfter()
    {
        return $this->retryAfter;
    }

    /**
     * Returns retryAfter
     *
     * @param ResponseInterface $responseObject
     */
    private function setRetryAfter(ResponseInterface $responseObject)
    {
        $responseHeader = $responseObject->getHeaders();
        if (array_keys($responseHeader, 'Retry-After')) {
            $this->retryAfter = $responseHeader['Retry-After'];
        }
    }
}
