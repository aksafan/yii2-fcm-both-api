<?php

namespace aksafan\fcm\helpers;

use aksafan\fcm\builders\StaticBuilderFactory;
use aksafan\fcm\responses\AbstractResponse;
use aksafan\fcm\responses\TopicSubscribeResponse;
use aksafan\fcm\responses\apiV1\TokenResponse;
use aksafan\fcm\responses\legacyApi\GroupManagementResponse;
use aksafan\fcm\responses\legacyApi\TokenResponse as LegacyTokenResponse;
use aksafan\fcm\responses\legacyApi\TopicResponse as LegacyTopicResponse;
use aksafan\fcm\responses\legacyApi\GroupResponse as LegacyGroupResponse;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ErrorsHelper.
 */
class ErrorsHelper
{
    const UNSPECIFIED_ERROR = 'UNSPECIFIED_ERROR';
    const INVALID_ARGUMENT = 'INVALID_ARGUMENT';
    const UNREGISTERED = 'UNREGISTERED';
    const NOT_FOUND = 'NOT_FOUND';
    const SENDER_ID_MISMATCH = 'SENDER_ID_MISMATCH';
    const QUOTA_EXCEEDED = 'QUOTA_EXCEEDED';
    const APNS_AUTH_ERROR = 'APNS_AUTH_ERROR';
    const UNAVAILABLE = 'UNAVAILABLE';
    const INTERNAL = 'INTERNAL';
    const STATUS_CODE_400 = 'status_code_400';
    const STATUS_CODE_403 = 'status_code_403';
    const OTHER_STATUS_CODES = 'other_status_codes';

    const ERROR_CODE_ENUMS = [
        self::UNSPECIFIED_ERROR => 'No more information is available about this error.',
        self::INVALID_ARGUMENT => 'Request parameters were invalid. An extension of type google.rpc.BadRequest is returned to specify which field was invalid.',
        self::UNREGISTERED => 'App instance was unregistered from FCM. This usually means that the token used is no longer valid and a new one must be used.',
        self::NOT_FOUND => 'App instance was unregistered from FCM. This usually means that the token used is no longer valid and a new one must be used.',
        self::SENDER_ID_MISMATCH => 'The authenticated sender ID is different from the sender ID for the registration token.',
        self::QUOTA_EXCEEDED => 'Sending limit exceeded for the message target. An extension of type google.rpc.QuotaFailure is returned to specify which quota got exceeded.',
        self::APNS_AUTH_ERROR => 'APNs certificate or auth key was invalid or missing.',
        self::UNAVAILABLE => 'The server is overloaded.',
        self::INTERNAL => 'An unknown internal error occurred.',
        self::STATUS_CODE_400 => 'Something in the request data was wrong: check if all data{...}values are converted to strings and look through logs',
        self::STATUS_CODE_403 => 'To use the new FCM HTTP v1 API, you need to enable FCM API on your Google API dashboard first - https://console.developers.google.com/apis/library/fcm.googleapis.com/.',
        self::OTHER_STATUS_CODES => 'Something happened with request to FCM. Check logs for more information.',
    ];

    const INVALID_FCM_RESPONSE = 'invalid_fcm_response';
    const PARSE_ERROR = 'parse_error';
    const GUZZLE_HTTP_CLIENT = 'guzzle_http_client';
    const GUZZLE_HTTP_CLIENT_ERROR = 'guzzle_http_client_error';
    const GUZZLE_HTTP_CLIENT_OTHER_ERRORS = 'guzzle_http_client_other_errors';

    /** @var array Possible logs' errors */
    const LOGS_ERRORS = [
        self::INVALID_FCM_RESPONSE,
        self::PARSE_ERROR,
        self::GUZZLE_HTTP_CLIENT,
        self::GUZZLE_HTTP_CLIENT_ERROR,
        self::GUZZLE_HTTP_CLIENT_OTHER_ERRORS,
    ];

    /**
     * @param string $errorCodeName
     *
     * @return string
     */
    public static function getFcmErrorDescription(string $errorCodeName): string
    {
        return array_key_exists($errorCodeName, self::ERROR_CODE_ENUMS) ? self::ERROR_CODE_ENUMS[$errorCodeName] : '';
    }

    /**
     * @param string $errorCodeName
     * @param string $additionalInfo
     *
     * @return string
     */
    public static function getFcmErrorMessage(string $errorCodeName, string $additionalInfo = ''): string
    {
        return 'FcmError['.$errorCodeName.']: '.self::getFcmErrorDescription($errorCodeName).(!empty($additionalInfo) ? '. Additional info: '.$additionalInfo : '');
    }

    /**
     * @param ClientException|null $e
     *
     * @return string
     */
    public static function getGuzzleClientExceptionMessage($e): string
    {
        $errorStatusCode = '';
        $reasonPhrase = '';
        $message = '';
        if (null !== $e->getResponse()) {
            $errorStatusCode = $e->getResponse()->getStatusCode();
            $reasonPhrase = $e->getResponse()->getReasonPhrase();
            $message = $e->getMessage();
        }

        return 'Guzzle ClientException has occurred. Status code = '.$errorStatusCode.'. Reason = '.$reasonPhrase.'Exception message = '.$message;
    }

    /**
     * @param GuzzleException $e
     *
     * @return string
     */
    public static function getGuzzleExceptionMessage(GuzzleException $e): string
    {
        return 'Guzzle Exception has occurred. Exception message = '.$e->getMessage().'. Trace = '.$e->getTraceAsString();
    }

    /**
     * @param string $statusCode
     * @param string $responseBody
     * @param AbstractResponse $response
     *
     * @return string
     */
    public static function getStatusCodeErrorMessage(string $statusCode, string $responseBody, AbstractResponse $response): string
    {
        return 'Http client '.$statusCode.', request reason is = '.self::getResponseType($response).'. Response = '.$responseBody;
    }

    /**
     * @param AbstractResponse $response
     *
     * @return string
     */
    private static function getResponseType(AbstractResponse $response): string
    {
        if ($response instanceof GroupManagementResponse) {
            return StaticBuilderFactory::FOR_GROUP_MANAGEMENT;
        }
        if ($response instanceof LegacyGroupResponse) {
            return StaticBuilderFactory::FOR_GROUP_SENDING;
        }
        if ($response instanceof LegacyTokenResponse) {
            return StaticBuilderFactory::FOR_TOKEN_SENDING;
        }
        if ($response instanceof LegacyTopicResponse) {
            return StaticBuilderFactory::FOR_TOPIC_SENDING;
        }
        if ($response instanceof TokenResponse) {
            return StaticBuilderFactory::FOR_TOKEN_SENDING;
        }
        if ($response instanceof TopicSubscribeResponse) {
            return StaticBuilderFactory::FOR_TOPIC_MANAGEMENT;
        }

        return StaticBuilderFactory::UNKNOWN_REASON;
    }
}
