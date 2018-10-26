<?php

namespace aksafan\fcm\builders;

use aksafan\fcm\source\helpers\OptionsHelper;
use InvalidArgumentException;

/**
 * Builder for Topic to (un)subscribe to(from) FCM both API versions.
 *
 * Class TopicSubscriptionOptionsBuilder
 *
 * @link https://firebase.google.com/docs/cloud-messaging/admin/manage-topic-subscriptions
 */
class TopicSubscriptionOptionsBuilder implements OptionsBuilder
{
    /**
     * @var string
     */
    private $topic;

    /**
     * @var bool
     */
    private $subscriptionStatus;

    /**
     * @var array
     */
    private static $tokens;

    /**
     * @param string $topic
     *
     * @throws InvalidArgumentException
     */
    public function setTopic(string $topic)
    {
        OptionsHelper::validateTopicValue($topic);
        $this->topic = $topic;
    }

    /**
     * @param array $tokens
     *
     * @throws InvalidArgumentException
     */
    public function setTokensForTopic(array $tokens)
    {
        OptionsHelper::validateTokensValue($tokens);
        self::$tokens = $tokens;
    }

    /**
     * @param boolean $subscriptionStatus Flag for subscribe (true) or unsubscribe (false) to (from) FCM topic.
     */
    public function setSubscribeToTopic(bool $subscriptionStatus)
    {
        $this->subscriptionStatus = $subscriptionStatus;
    }

    /**
     * @return null|string
     */
    public function getTopic()
    {
        return $this->topic ?? null;
    }

    /**
     * @return array
     */
    public static function getTokens(): array
    {
        return self::$tokens ?? [];
    }

    /**
     * @return bool
     */
    public function getSubscriptionStatus(): bool
    {
        return $this->subscriptionStatus;
    }

    /**
     * Builds request body data.
     *
     * @return array
     */
    public function build(): array
    {
        return self::$tokens;
    }
}
