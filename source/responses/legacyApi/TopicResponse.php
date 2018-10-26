<?php

namespace aksafan\fcm\responses\legacyApi;

/**
 * Class TopicResponse.
 */
class TopicResponse extends LegacyAbstractResponse implements TopicResponseInterface
{
    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @param array $responseBody
     */
    public function parseResponse(array $responseBody) //TODO check response
    {
        if (array_key_exists(self::MESSAGE_ID, $responseBody)) {
            $this->setMessageId($responseBody[self::MESSAGE_ID]);
            $this->setResult(true);
        }

        if (array_key_exists(self::ERROR, $responseBody)) {
            $this->setErrorMessage($responseBody[self::ERROR]);
            $this->setErrorStatusDescription($responseBody[self::ERROR]);
        }
    }

    /**
     * Returns the error message from sending push to topic(s).
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Sets the error message from sending push to topic(s).
     *
     * @param string $errorMessage
     */
    private function setErrorMessage(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }
}
