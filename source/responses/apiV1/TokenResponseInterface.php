<?php

namespace aksafan\fcm\responses\apiV1;

/**
 * Interface TokenResponseInterface.
 */
interface TokenResponseInterface
{
    /**
     * Returns rawMessageId - the raw message response from FMC.
     *
     * @return string
     */
    public function getRawMessageId();

    /**
     * Returns if there was an error during sending push notification.
     *
     * @return bool
     */
    public function getError(): bool;

    /**
     * Returns FCM APIv1 error status.
     *
     * @return string
     */
    public function getErrorStatus();

    /**
     * Returns FCM APIv1 error code.
     *
     * Official google documentation:
     *
     * @link https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode
     *
     * @return int
     */
    public function getErrorCode();

    /**
     * Returns FCM APIv1 error message.
     *
     * @return string
     */
    public function getErrorMessage();

    /**
     * Returns FCM APIv1 error details.
     *
     * @return array
     */
    public function getErrorDetails(): array;

    /**
     * Returns token to delete.
     *
     * remove all tokens returned by this method in your database
     *
     * @return array
     */
    public function getTokensToDelete();
}
