<?php

namespace aksafan\fcm\responses;

use aksafan\fcm\builders\TopicSubscriptionOptionsBuilder;
use aksafan\fcm\helpers\ErrorsHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TopicSubscribeResponse.
 */
class TopicSubscribeResponse extends AbstractResponse implements TopicSubscribeResponseInterface
{
    const RESULTS = 'results';

    /**
     * @var array
     */
    private $tokensWithError = [];

    /**
     * Returns tokens that was unsuccessfully (un)subscribe to topic with their corresponded errors.
     *
     * @return array
     */
    public function getTopicTokensWithError(): array
    {
        return $this->tokensWithError;
    }

    /**
     * Parses the response from (un)subscribing to(from) topic.
     *
     * @param array $responseBody
     */
    public function parseResponse(array $responseBody)  //TODO check response or rename, need to clarify logic
    {
        if (array_key_exists(self::RESULTS, $responseBody) && \is_array($results = $responseBody[self::RESULTS])) {
            $tokens = TopicSubscriptionOptionsBuilder::getTokens();
            $tokensWithError = [];
            /** @var array $results */
            foreach ($results as $id => $result) {
                if (\is_array($result) && ! empty($result) && array_key_exists($id, $tokens) && array_key_exists('error', $result)) {
                    $this->setTokensWithError([
                        'token' => $tokens[$id],
                        'error' => $result['error'],
                    ]);
                }
            }
            $this->tokensWithError = $tokensWithError;
        }
        if (empty($this->tokensWithError)) {
            $this->setResult(true);
        }
    }

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

        return false;
    }

    /**
     * Sets tokens that was unsuccessfully (un)subscribe to topic with their corresponded errors.
     *
     * @param array $tokensWithError
     */
    public function setTokensWithError(array $tokensWithError)
    {
        $this->tokensWithError[] = $tokensWithError;
    }


}
