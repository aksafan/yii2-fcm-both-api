<?php

namespace aksafan\fcm\source\responses\legacyApi;

use aksafan\fcm\source\builders\legacyApi\MessageOptionsBuilder;
use aksafan\fcm\source\helpers\ErrorsHelper;

/**
 * Class TokenResponse.
 */
class TokenResponse extends LegacyAbstractResponse implements TokenResponseInterface
{
    const MULTICAST_ID = 'multicast_id';
    const CANONICAL_IDS = 'canonical_ids';
    const MISSING_REGISTRATION = 'MissingRegistration';
    const REGISTRATION_ID = 'registration_id';
    const NOT_REGISTERED = 'NotRegistered';
    const INVALID_REGISTRATION = 'InvalidRegistration';
    const UNAVAILABLE = 'Unavailable';
    const DEVICE_MESSAGE_RATE_EXCEEDED = 'DeviceMessageRateExceeded';
    const INTERNAL_SERVER_ERROR = 'InternalServerError';

    /**
     * @var int
     */
    protected $numberTokensSuccess = 0;

    /**
     * @var int
     */
    protected $numberTokensFailure = 0;

    /**
     * @var int
     */
    protected $numberTokenModify = 0;

    /**
     * @var array
     */
    protected $tokensToDelete = [];

    /**
     * @var array
     */
    protected $tokensToModify = [];

    /**
     * @var array
     */
    protected $tokensToRetry = [];

    /**
     * @var array
     */
    protected $tokensWithError = [];

    /**
     * @var bool
     */
    protected $hasMissingToken = false;

    /**
     * Get the number of device reached with success.
     *
     * @return int
     */
    public function getNumberSuccess()
    {
        return $this->numberTokensSuccess;
    }

    /**
     * Get the number of device which thrown an error.
     *
     * @return int
     */
    public function getNumberFailure()
    {
        return $this->numberTokensFailure;
    }

    /**
     * Get the number of device that you need to modify their token.
     *
     * @return int
     */
    public function getNumberModification()
    {
        return $this->numberTokenModify;
    }

    /**
     * get token to delete.
     *
     * remove all tokens returned by this method in your database
     *
     * @return array
     */
    public function getTokensToDelete()
    {
        return $this->tokensToDelete;
    }

    /**
     * get token to modify.
     *
     * key: oldToken
     * value: new token
     *
     * find the old token in your database and replace it with the new one
     *
     * @return array
     */
    public function getTokensToModify()
    {
        return $this->tokensToModify;
    }

    /**
     * Get tokens that you should resend using exponential backoof.
     *
     * @return array
     */
    public function getTokensToRetry()
    {
        return $this->tokensToRetry;
    }

    /**
     * Get tokens that thrown an error.
     *
     * key : token
     * value : error
     *
     * In production, remove these tokens from you database
     *
     * @return array
     */
    public function getTokensWithError()
    {
        return $this->tokensWithError;
    }

    /**
     * check if missing tokens was given to the request
     * If true, remove all the empty token in your database.
     *
     * @return bool
     */
    public function hasMissingToken()
    {
        return $this->hasMissingToken;
    }

    /**
     * Parses the response from sending message.
     *
     * @param array $responseBody
     */
    public function parseResponse(array $responseBody)
    {
        if (array_key_exists(self::MULTICAST_ID, $responseBody)) {
            $this->setMessageId((string) $responseBody[self::MULTICAST_ID]);
        }

        if (array_key_exists(self::SUCCESS, $responseBody)) {
            $this->setNumberSuccess((int) $responseBody[self::SUCCESS]);
        }

        if (array_key_exists(self::FAILURE, $responseBody)) {
            $this->setNumberFailure((int) $responseBody[self::FAILURE]);
        }

        if (array_key_exists(self::CANONICAL_IDS, $responseBody)) {
            $this->setNumberModification((int) $responseBody[self::CANONICAL_IDS]);
        }

        if ($this->needResultParsing($responseBody)) {
            $this->parseResult($responseBody);
        } else {
            $this->setResult(true);
        }
    }

    /**
     * @param array $responseBody
     */
    private function parseResult(array $responseBody)
    {
        if (! \is_array($results = $responseBody[self::RESULTS])) {
            \Yii::error('Parse error. ResponseBody = '.json_encode($responseBody), ErrorsHelper::PARSE_ERROR);
        }
        /** @var array $results */
        foreach ($results as $index => $result) {
            if (\is_array($result)) {
                if (array_key_exists(self::ERROR, $result)) {
                    $this->setErrorStatusDescription($result[self::ERROR]);
                }
                if (
                    !$this->isSent($result) &&
                    !$this->needToBeModify($index, $result) &&
                    !$this->needToBeDeleted($index, $result) &&
                    !$this->needToResend($index, $result) &&
                    !$this->checkMissingToken($result)
                ) {
                    $this->addErrors($index, $result);
                }
            }
        }
    }

    /**
     * Sets the number of device reached with success.
     *
     * @param int $numberTokensSuccess
     */
    private function setNumberSuccess(int $numberTokensSuccess)
    {
        $this->numberTokensSuccess = $numberTokensSuccess;
    }

    /**
     * Sets the number of device which thrown an error.
     *
     * @param int $numberTokensFailure
     */
    private function setNumberFailure(int $numberTokensFailure)
    {
        $this->numberTokensFailure = $numberTokensFailure;
    }

    /**
     * Sets the number of device that you need to modify their token.
     *
     * @param int $numberTokenModify
     */
    private function setNumberModification(int $numberTokenModify)
    {
        $this->numberTokenModify = $numberTokenModify;
    }

    /**
     * @param array $responseBody
     *
     * @return bool
     */
    private function needResultParsing(array $responseBody): bool
    {
        return array_key_exists(self::RESULTS, $responseBody) && ($this->numberTokensFailure > 0 || $this->numberTokenModify > 0);
    }

    /**
     * @param array $results
     *
     * @return bool
     */
    private function isSent(array $results): bool
    {
        return array_key_exists(self::MESSAGE_ID, $results) && !array_key_exists(self::REGISTRATION_ID, $results);
    }

    /**
     * @param $index
     * @param array $result
     *
     * @return bool
     */
    private function needToBeModify($index, array $result): bool
    {
        if (array_key_exists(self::MESSAGE_ID, $result) && array_key_exists(self::REGISTRATION_ID, $result)) {
            $tokens = MessageOptionsBuilder::getTokens();
            if (array_key_exists($index, $tokens)) {
                $this->tokensToModify[$tokens[$index]] = $result[self::REGISTRATION_ID];
            }

            return true;
        }

        return false;
    }

    /**
     * @param $index
     * @param array $result
     *
     * @return bool
     */
    private function needToBeDeleted($index, array $result): bool
    {
        if (array_key_exists(self::ERROR, $result) && (\in_array(self::NOT_REGISTERED, $result, true) || \in_array(self::INVALID_REGISTRATION, $result, true))) {
            $tokens = MessageOptionsBuilder::getTokens();
            if (array_key_exists($index, $tokens)) {
                $this->tokensToDelete[] = $tokens[$index];
            }

            return true;
        }

        return false;
    }

    /**
     * @param $index
     * @param array $result
     *
     * @return bool
     */
    private function needToResend($index, array $result): bool
    {
        if (array_key_exists(self::ERROR, $result) && (\in_array(self::UNAVAILABLE, $result, true) || \in_array(self::DEVICE_MESSAGE_RATE_EXCEEDED, $result, true) || \in_array(self::INTERNAL_SERVER_ERROR, $result, true))) {
            $tokens = MessageOptionsBuilder::getTokens();
            if (array_key_exists($index, $tokens)) {
                $this->tokensToRetry[] = $tokens[$index];
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $result
     *
     * @return bool
     */
    private function checkMissingToken(array $result): bool
    {
        $hasMissingToken = (array_key_exists(self::ERROR, $result) && \in_array(self::MISSING_REGISTRATION, $result, true));

        $this->hasMissingToken = (bool) ($this->hasMissingToken | $hasMissingToken);

        return $hasMissingToken;
    }

    /**
     * @param $index
     * @param array $result
     */
    private function addErrors($index, array $result)
    {
        $tokens = MessageOptionsBuilder::getTokens();
        if (array_key_exists(self::ERROR, $result) && array_key_exists($index, $tokens) && $tokens[$index]) {
            $this->tokensWithError[$tokens[$index]] = $result[self::ERROR];
        }
    }
}
