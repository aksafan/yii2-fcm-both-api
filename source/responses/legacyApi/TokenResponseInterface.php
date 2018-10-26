<?php

namespace aksafan\fcm\responses\legacyApi;

/**
 * Interface TokenResponseInterface.
 */
interface TokenResponseInterface
{
    /**
     * Returns the number of device reached with success.
     *
     * @return int
     */
    public function getNumberSuccess();

    /**
     * Returns the number of device which thrown an error.
     *
     * @return int
     */
    public function getNumberFailure();

    /**
     * Returns the number of device that you need to modify their token.
     *
     * @return int
     */
    public function getNumberModification();

    /**
     * Returns token to delete.
     *
     * remove all tokens returned by this method in your database
     *
     * @return array
     */
    public function getTokensToDelete();

    /**
     * Returns token to modify.
     *
     * key: oldToken
     * value: new token
     *
     * find the old token in your database and replace it with the new one
     *
     * @return array
     */
    public function getTokensToModify();

    /**
     * Returns tokens that you should resend using exponential backoof.
     *
     * @return array
     */
    public function getTokensToRetry();

    /**
     * Returns tokens that thrown an error.
     *
     * key : token
     * value : error
     *
     * In production, remove these tokens from you database
     *
     * @return array
     */
    public function getTokensWithError();

    /**
     * Checks if missing tokens was given to the request.
     * If true, remove all the empty token in your database.
     *
     * @return bool
     */
    public function hasMissingToken();
}
