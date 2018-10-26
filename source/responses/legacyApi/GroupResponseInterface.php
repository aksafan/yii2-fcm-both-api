<?php

namespace aksafan\fcm\source\responses\legacyApi;

/**
 * Interface GroupResponseInterface.
 */
interface GroupResponseInterface
{
    /**
     * Returns the number of token successfully sent.
     *
     * @return int
     */
    public function getNumberSuccess();

    /**
     * Returns the number of token unsuccessfully sent.
     *
     * @return int
     */
    public function getNumberFailure();

    /**
     * Returns an array of tokens unsuccessfully sent.
     *
     * @return array
     */
    public function getTokensFailed(): array;
}
