<?php

namespace aksafan\fcm\responses\legacyApi;

/**
 * Class GroupResponse.
 */
class GroupResponse extends LegacyAbstractResponse implements GroupResponseInterface
{
    const FAILED_REGISTRATION_IDS = 'failed_registration_ids';

    /**
     * @var int
     */
    private $numberSuccess = 0;

    /**
     * @var int
     */
    private $numberFailure = 0;

    /**
     * @var array
     */
    private $tokensFailed = [];

    /**
     * @param array $responseBody
     */
    public function parseResponse(array $responseBody) //TODO check response
    {
        if (array_key_exists(self::SUCCESS, $responseBody)) {
            $this->setNumberSuccess((int) $responseBody[self::SUCCESS]);
        }
        if (array_key_exists(self::FAILURE, $responseBody)) {
            $this->setNumberFailure((int) $responseBody[self::FAILURE]);
        }
        if ($this->getNumberFailure() > 0) {
            if (array_key_exists(self::FAILED_REGISTRATION_IDS, $responseBody) && \is_array($failedRegistrationIds = $responseBody[self::FAILED_REGISTRATION_IDS])) {
                foreach ($failedRegistrationIds as $registrationId) {
                    $this->setTokensFailed((string) $registrationId);
                }
            }
        } else {
            $this->setResult(true);
        }
    }

    /**
     * Returns the number of token successfully sent.
     *
     * @return int
     */
    public function getNumberSuccess()
    {
        return $this->numberSuccess;
    }

    /**
     * Returns the number of token unsuccessfully sent.
     *
     * @return int
     */
    public function getNumberFailure()
    {
        return $this->numberFailure;
    }

    /**
     * Returns an array of tokens unsuccessfully sent.
     *
     * @return array
     */
    public function getTokensFailed(): array
    {
        return $this->tokensFailed;
    }

    /**
     * Sets the number of token successfully sent.
     *
     * @param int $numberSuccess
     */
    private function setNumberSuccess(int $numberSuccess)
    {
        $this->numberSuccess = $numberSuccess;
    }

    /**
     * Sets the number of token unsuccessfully sent.
     *
     * @param int $numberFailure
     */
    private function setNumberFailure(int $numberFailure)
    {
        $this->numberFailure = $numberFailure;
    }

    /**
     * Sets the number of token unsuccessfully sent.
     *
     * @param string $token
     */
    private function setTokensFailed(string $token)
    {
        $this->tokensFailed[] = $token;
    }
}
