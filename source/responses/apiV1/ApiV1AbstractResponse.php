<?php

namespace aksafan\fcm\responses\apiV1;

use aksafan\fcm\helpers\ErrorsHelper;
use aksafan\fcm\responses\AbstractResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiV1AbstractResponse.
 */
abstract class ApiV1AbstractResponse extends AbstractResponse
{
    const RESULTS = 'results';

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

        $responseContents = $response->getBody()->getContents();
        if (200 === ($statusCode = $response->getStatusCode())) {
            return true;
        }

        if (404 === $statusCode) {
            $decodedResponseContents = $this->getResponseBody($response);
            if (isset($decodedResponseContents['error']['message']) && 'Requested entity was not found.' === $decodedResponseContents['error']['message']) {
                return true;
            }
        }

        if (400 === $statusCode) {
            \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, $responseContents, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            \Yii::error('Something in the request data was wrong: check if all data{...}values are converted to strings.', ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            $this->setErrorStatusDescription(ErrorsHelper::STATUS_CODE_400, $responseContents);

            return false;
        }

        if (403 === $statusCode) {
            \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, self::UNAUTHORIZED_REQUEST_EXCEPTION_MESSAGE, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            \Yii::error('To use the new FCM HTTP v1 API, you need to enable FCM API on your Google API dashboard first - https://console.developers.google.com/apis/library/fcm.googleapis.com/.', ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            $this->setErrorStatusDescription(ErrorsHelper::STATUS_CODE_403, $responseContents);

            return false;
        }

        \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, $responseContents, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_OTHER_ERRORS);
        $this->setErrorStatusDescription(ErrorsHelper::OTHER_STATUS_CODES, $responseContents);

        return false;
    }
}
