# yii2-fcm-both-api
Yii2 Extension for sending push notification with both [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging/) (FCM) HTTP Server Protocols (APIs).


[![Latest Stable Version](https://poser.pugx.org/aksafan/yii2-fcm-both-api/v/stable)](https://packagist.org/packages/aksafan/yii2-fcm-both-api)
[![Total Downloads](https://poser.pugx.org/aksafan/yii2-fcm-both-api/downloads)](https://packagist.org/packages/aksafan/yii2-fcm-both-api)
[![Build Status](https://travis-ci.org/aksafan/yii2-fcm-both-api.svg?branch=master)](https://travis-ci.org/aksafan/yii2-fcm-both-api)

This extension supports sending push notification through both currently supported FCM API versions:
- [HTTP v1 API](https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages)
- [Legacy HTTP Server Protocol](https://firebase.google.com/docs/cloud-messaging/http-server-ref)

> Note: The XMPP protocol is not currently supported.

# Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/). Check the [composer.json](https://github.com/aksafan/yii2-fcm-both-api/blob/master/composer.json) for this extension's requirements and dependencies. Read this [composer.json](https://github.com/aaronpk/emoji-detector-php/blob/master/composer.json) for source library requirements.

To install, either run

```
$ php composer.phar require aksafan/yii2-fcm-both-api
```

or add

```
"aksafan/yii2-fcm-both-api": "*"
```

to the `require` section of your `composer.json` file.


Configuration
-------------

##### In order to use this library, you have to configure the Fcm class in your application configuration.

For ApiV1:

```php
return [
    //....
    'components' => [
        'fcm' => [
             'class' => 'aksafan\emoji\source\Fcm',
             'apiVersion' => \aksafan\fcm\requests\StaticRequestFactory::API_V1,
             'apiParams' => [
                 'privateKeyFile' => '/path/to/your/file/privateKeyFile.json',
             ],
        ],
    ]
];
```

> `privateKeyFile` - used to authenticate the service account and authorize it to access Firebase services. You must [generate](https://firebase.google.com/docs/cloud-messaging/auth-server#authorize_http_v1_send_requests) a private key file in JSON format and use this key to retrieve a short-lived OAuth 2.0 token.

For Legacy API:

```php
return [
    //....
    'components' => [
        'fcm' => [
             'class' => 'aksafan\emoji\source\Fcm',
             'apiVersion' => \aksafan\fcm\requests\StaticRequestFactory::LEGACY_API,
             'apiParams' => [
                 'serverKey' => 'aef',
                 'senderId' => 'fwef',
             ],
        ],
    ]
];
```
> `serverKey` - a server key that authorizes your app server for access to Google services, including sending messages via the Firebase Cloud Messaging legacy protocols. You obtain the server key when you create your Firebase project. You can view it in the [Cloud Messaging](https://console.firebase.google.com/project/_/settings/cloudmessaging/) tab of the Firebase console Settings pane.

> `senderId` - a unique numerical value created when you create your Firebase project, available in the [Cloud Messaging](https://console.firebase.google.com/project/_/settings/cloudmessaging/) tab of the Firebase console Settings pane. The sender ID is used to identify each sender that can send messages to the client app.

##### Also add this to your Yii.php file in the root directory of the project for IDE code autocompletion.

```php
/**
 * Class WebApplication
 * Include only Web application related components here.
 *
 * @property \aksafan\emoji\source\Fcm $fcm
 */
class WebApplication extends yii\web\Application
{
}
```

##### Now you can get access to extension's methods through:

```php
Yii::$app->fcm
```


Basic Usage
-----------

> **_N.B._** _The main thing about this library is to be used in queues, so we tried to balance between OOP and low amount of objects used._ 

#### APIv1

_The APIv1 part of extension can send:_
1. A message to a [specific token (device)](https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_specific_devices).
2. A message to a given [topic](https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_topics).
3. A message to several [topics by condition](https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_topics).

_A message can contain:_
1. Two types of [messages](https://firebase.google.com/docs/cloud-messaging/concept-options#notifications_and_data_messages): [Notification messages](https://firebase.google.com/docs/cloud-messaging/concept-options#notifications) and [Data messages](https://firebase.google.com/docs/cloud-messaging/concept-options#data_messages) (both are optional).
2. Their [combination](https://firebase.google.com/docs/cloud-messaging/concept-options#notification-messages-with-optional-data-payload).
3. Target platform specific [configuration](https://firebase.google.com/docs/cloud-messaging/concept-options#customizing_a_message_across_platforms).

##### Send push-notification to a single token (device)
You need to have a registration token for the target device. Registration tokens are strings generated by the client FCM SDKs.
Each of the Firebase client SDKs are able to generate these registration tokens: [iOS](https://firebase.google.com/docs/cloud-messaging/ios/client#access_the_registration_token), [Android](https://firebase.google.com/docs/cloud-messaging/android/client#sample-register), [Web](https://firebase.google.com/docs/cloud-messaging/js/client#access_the_registration_token). 
In order to sent push to the single token you need to use `setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')` method with `\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN` constant:

```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->send();
```

##### Send push-notification to a topic
Based on the publish/subscribe model, FCM topic messaging allows you to send a message to multiple devices that have opted in to a particular topic. You compose topic messages as needed, and FCM handles routing and delivering the message reliably to the right devices.

> To (un)subscribe devices to a topic look for `Topic management` section below

For example, users of a local weather forecasting app could opt in to a “severe weather alerts” topic and receive notifications of storms threatening specified areas. Users of a sports app could subscribe to automatic updates in live game scores for their favorite teams.

_The main things to keep in mind about topics:_
 - Developers can choose any topic name that matches the regular expression: `[a-zA-Z0-9-_.~%]+`.
 - Topic messaging supports unlimited topics and subscriptions for each app.
 - Topic messaging is best suited for content such as news, weather, or other publicly available information.
 - Topic messages are optimized for throughput rather than latency. For fast, secure delivery to single devices or small groups of devices, target messages to registration tokens, **not topics**.

In order to sent push to the topic you need to use `setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOPIC, 'your_token')` method with `\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOPIC` constant:

```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOPIC, 'some-topic')
    ->send();
```

##### Send push-notification to a combination of topics by condition
The condition is a boolean expression that specifies the target topics.
For example, the following condition will send messages to devices that are subscribed to `'TopicA'` and either `'TopicB'` or `'TopicC'`:
```php
"'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)"
```
FCM first evaluates any conditions in parentheses, and then evaluates the expression from left to right.
In the above expression, a user subscribed to any single topic does not receive the message.
Likewise, a user who does not subscribe to TopicA does not receive the message.
These combinations do receive it:
 - `TopicA` and `TopicB`
 - `TopicA` and `TopicC`

In order to sent push to the several topics by condition you need to use `setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOPIC_CONDITION, 'your_token')` method with `\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOPIC_CONDITION` constant:

```php
$condition = "'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)";
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOPIC_CONDITION, $condition)
    ->send();
```

##### Send push-notification with only 'Data' message type.

```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->setData(['a' => '1', 'b' => 'test'])
    ->send();
```

##### Send push-notification with only 'Notification' message type.

```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->setNotification('Test Title', 'Test Description')
    ->send();
```


##### Send push-notification with both 'Notification' and 'Data' message type.

```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->setData(['a' => '1', 'b' => 'test'])
    ->setNotification('Test Title', 'Test Description')
    ->send();
```

##### Send push-notification without 'Notification' and 'Data' message type.

```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->send();
```

##### Send push-notification with platform specific configuration.

###### Android [config](https://firebase.google.com/docs/cloud-messaging/admin/send-messages#android-specific_fields):
```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->setAndroidConfig([
        'ttl' => '3600s',
        'priority' => 'normal',
        'notification' => [
            'title' => 'Android Title',
            'body' => 'Android Description.',
            'icon' => 'stock_ticker_update',
            'color' => '#ff0000',
        ],
    ])
    ->send();
```

###### APNs [config](https://firebase.google.com/docs/cloud-messaging/admin/send-messages#apns-specific_fields):
```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->setApnsConfig([
        'headers' => [
            'apns-priority' => '10',
        ],
        'payload' => [
            'aps' => [
                'alert' => [
                    'title' => 'iOS Title',
                    'body' => 'iOS Description.',
                ],
                'badge' => 42,
            ],
        ],
    ])
    ->send();
```

###### Web-push [config](https://firebase.google.com/docs/cloud-messaging/admin/send-messages#webpush-specific_fields):
```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\apiV1\MessageOptionsBuilder::TOKEN, 'your_token')
    ->setWebPushConfig([
        'notification' => [
            'title' => 'Web push Title',
            'body' => 'Web push Description.',
            'icon' => 'https://my-server/icon.png',
        ],
    ])
    ->send();
```

###### All together [config](https://firebase.google.com/docs/cloud-messaging/admin/send-messages#putting_it_all_together):
```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => 'test'])
    ->setNotification('Test Title', 'Test Description')
    ->setAndroidConfig([
        'ttl' => '3600s',
        'priority' => 'normal',
        'notification' => [
            'title' => 'Android Title',
            'body' => 'Andorid Description.',
            'icon' => 'push_icon',
            'color' => '#ff0000',
        ],
    ])
    ->setApnsConfig([
        'headers' => [
            'apns-priority' => '10',
        ],
        'payload' => [
            'aps' => [
                'alert' => [
                    'title' => 'iOS Title',
                    'body' => 'iOS Description.',
                ],
                'badge' => 42,
            ],
        ],
    ])
    ->setWebPushConfig([
        'notification' => [
            'title' => 'Web push Title',
            'body' => 'Web push Description.',
            'icon' => 'https://my-server/icon.png',
        ],
    ])
    ->send();
```

> **_N.B._** _Pay attention that platform specific config will replace the general one._

In example above Android client will receive this notification info: 
```php
'title' => 'Android Title',
'body' => 'Android Description.'
```
and **NOT** this:
```php
'title' => 'Test Title',
'body' => 'Test Description.'
```

##### Handling response.

After sending the request to FCM you will get an instance of `\aksafan\fcm\responses\apiV1\TokenResponse`.
```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => 'test'])
    ->setNotification('Test Title', 'Test Description')
    ->send();
    
if ($result->isResultOk()) {
    echo $result->getRawMessageId().PHP_EOL;
    echo $result->getMessageId();
} else {
    $tokensToDelete = $result->getTokensToDelete();
    $errorDetails = $result->getErrorDetails();
    echo $result->getErrorStatusDescription();
}
```
If the result is OK you can get raw message from FCM in format - `projects/your_project_id/messages/message_id`:
```php
$result->getRawMessageId();
```
or only the message ID in format - `message_id`:
```php
$result->getMessageId();
```
If something has happened, you can get error description with the information about the problem:
```php
$result->getErrorStatusDescription();
```
Also tokens, that should be deleted from your DB, if the problem was with invalid tokens:
```php
$result->getTokensToDelete();
```
As well as the technical information from FCM:
```php
$result->getError();
$result->getErrorStatus();
$result->getErrorCode();
$result->getErrorMessage();
$result->getErrorDetails();
```

##### Validating messages ([dry run mode](https://firebase.google.com/docs/cloud-messaging/admin/send-messages#sending_in_the_dry_run_mode)).

You can validate a message by sending a validation-only request to the Firebase REST API.
```php
/** @var \aksafan\fcm\responses\apiV1\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => 'test'])
    ->setNotification('Test Title', 'Test Description')
    ->validateOnly()
    ->send();
    
if ($result->isResultOk()) {
    echo $result->getRawMessageId();
} else {
    echo $result->getErrorStatusDescription();
}
```
If the message is invalid, you will receive info in error description:
```php
$result->getErrorStatusDescription()
```
If it is valid, you will get fake_message - `projects/your_project_id/messages/fake_message_id`:
```php
$result->getMessageId();
```

#### Legacy API

_The Legacy API part of extension can send:_
1. A message to a [specific token (device)](https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_specific_devices_2).
2. A message to a several tokens (devices).
3. A message to [device group](https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_device_groups).
4. A message to a given [topic](https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_topics_2).
5. A message to several [topics by condition](https://firebase.google.com/docs/cloud-messaging/send-message#send_messages_to_topics_2).

_A message can contain:_
1. Two types of [messages](https://firebase.google.com/docs/cloud-messaging/concept-options#notifications_and_data_messages): [Notification messages](https://firebase.google.com/docs/cloud-messaging/concept-options#notifications) and [Data messages](https://firebase.google.com/docs/cloud-messaging/concept-options#data_messages) (both are optional).
2. Their [combination](https://firebase.google.com/docs/cloud-messaging/concept-options#notification-messages-with-optional-data-payload).
3. Target platform specific [configuration](https://firebase.google.com/docs/cloud-messaging/concept-options#customizing_a_message_across_platforms).

> Note: Legacy HTTP Server Protocol is still under support, is used by many people and is not in trouble of being deprecated, but Google aimed us to use HTTP v1 API.

##### Send push-notification to a single token (device)
You need to have a registration token for the target device. Registration tokens are strings generated by the client FCM SDKs.
Each of the Firebase client SDKs are able to generate these registration tokens: [iOS](https://firebase.google.com/docs/cloud-messaging/ios/client#access_the_registration_token), [Android](https://firebase.google.com/docs/cloud-messaging/android/client#sample-register), [Web](https://firebase.google.com/docs/cloud-messaging/js/client#access_the_registration_token). 

In order to sent push to the single token you need to use `setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, 'your_token')` method with `\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN` constant:

```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => '2'])
    ->setNotification('Send push-notification to a single token (device)', 'Test description')
    ->send();
```

##### Send push-notification to multiple tokens (devices)
Max amount of tokens in one request is 1000.
In order to sent push to the single token you need to use `setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKENS, ['your_token_1', 'your_token_2', 'your_token_3'])` method with `\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKENS` constant:

```php
$tokens = [
    'your_token_1',
    'your_token_2',
    'your_token_3',
];
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKENS, $tokens)
    ->setData(['a' => '1', 'b' => '2'])
    ->setNotification('Send push-notification to multiple tokens (devices)', 'Test description')
    ->send();
```

##### Send push-notification to a topic
Based on the publish/subscribe model, FCM topic messaging allows you to send a message to multiple devices that have opted in to a particular topic. You compose topic messages as needed, and FCM handles routing and delivering the message reliably to the right devices.

For example, users of a local weather forecasting app could opt in to a “severe weather alerts” topic and receive notifications of storms threatening specified areas. Users of a sports app could subscribe to automatic updates in live game scores for their favorite teams.

_The main things to keep in mind about topics:_
 - Developers can choose any topic name that matches the regular expression: `[a-zA-Z0-9-_.~%]+`.
 - Topic messaging supports unlimited topics and subscriptions for each app.
 - Topic messaging is best suited for content such as news, weather, or other publicly available information.
 - Topic messages are optimized for throughput rather than latency. For fast, secure delivery to single devices or small groups of devices, target messages to registration tokens, **not topics**.

In order to sent push to the topic you need to use `setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOPIC, 'your_token')` method with `\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOPIC` constant
and `createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_SENDING)` with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_SENDING` constant:

```php
/** @var \aksafan\fcm\responses\legacyApi\TopicResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_SENDING)
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOPIC, 'a-topic')
    ->setNotification('Test title', 'Test description')
    ->send();
```

##### Send push-notification to a combination of topics by condition
The condition is a boolean expression that specifies the target topics.
For example, the following condition will send messages to devices that are subscribed to `'TopicA'` and either `'TopicB'` or `'TopicC'`:
```php
"'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)"
```
FCM first evaluates any conditions in parentheses, and then evaluates the expression from left to right.
In the above expression, a user subscribed to any single topic does not receive the message.
Likewise, a user who does not subscribe to TopicA does not receive the message.
These combinations do receive it:
 - `TopicA` and `TopicB`
 - `TopicA` and `TopicC`

In order to sent push to the several topics by condition you need to use `setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOPIC_CONDITION, 'your_token')` method with `\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOPIC_CONDITION` constant:
and `createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_SENDING)` with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_SENDING` constant:

```php
$condition = "'a-topic' in topics && ('b-topic' in topics || 'b-topic' in topics)";
/** @var \aksafan\fcm\responses\legacyApi\TopicResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_SENDING)
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOPIC_CONDITION, $condition)
    ->setNotification('Test title', 'Test description')
    ->send();
```

##### Send push-notification to a group of tokens (devices)
FCM device groups allows you to send a message to multiple devices that have opted in to a particular group. You compose group of devices as needed, and FCM handles routing and delivering the message reliably to the right devices.

In order to sent push to the topic you need to use `setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::GROUP, 'your_notification_key')` method with `\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::GROUP` constant:
and `createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_SENDING)` with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_SENDING` constant:
```php
$notificationKey = 'your_notification_key';
/** @var \aksafan\fcm\responses\legacyApi\GroupResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_SENDING)
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::GROUP, $notificationKey)
    ->setData(['a' => '1', 'b' => '2'])
    ->setNotification('Test title', 'Test description')
    ->send();
```

##### Send push-notification with only 'Data' message type.

```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => 'test'])
    ->send();
```

##### Send push-notification with only 'Notification' message type.

```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setNotification('Test title', 'Test Description')
    ->send();
```

##### Send push-notification with both 'Notification' and 'Data' message type.

```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => 'test'])
    ->setNotification('Send push-notification with both \'Notification\' and \'Data\' message type.', 'Test Description')
    ->send();
```

##### Send push-notification without 'Notification' and 'Data' message type.

```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = Yii::$app
    ->fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, 'your_token')
    ->send();
```

##### Send push-notification with platform specific configuration.

###### Android [config](https://firebase.google.com/docs/cloud-messaging/http-server-ref#table2b):
```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setAndroidConfig([
        'title' => 'Android Title',
        'body' => 'Android Description.',
        'icon' => 'stock_ticker_update',
        'color' => '#ff0000',
    ])
    ->setPriority(\aksafan\fcm\source\helpers\OptionsHelper::HIGH)
    ->send();
```

###### APNs [config](https://firebase.google.com/docs/cloud-messaging/http-server-ref#table2a):
```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setApnsConfig([
        'title' => 'iOS Title',
        'body' => 'iOS Description.',
        'title_loc_key' => 'iOS Title loc key.',
        'badge' => '42',
        'sound' => 'bingbong.aiff',
    ])
    ->send();
```

###### Web-push [config](https://firebase.google.com/docs/cloud-messaging/http-server-ref#table2c):
```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setWebPushConfig([
        'title' => 'Web push Title',
        'body' => 'Web push Description.',
        'icon' => 'https://my-server/icon.png',
        'click_action' => 'click-action',
    ])
    ->send();
```

###### All together [config](https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support).
You can set additional configurations with these methods:
```php
    ->setCollapseKey(')
    ->setPriority()
    ->setContentAvailable()
    ->setMutableContent()
    ->setTimeToLive()
    ->setRestrictedPackageName()
    ->validateOnly()
```
All together can be:
```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => '2'])
    ->setNotification('Test title', 'Test description')
    ->setAndroidConfig([
        'title' => 'Android Title',
        'body' => 'Android Description.',
        'icon' => 'stock_ticker_update',
        'color' => '#ff0000',
    ])
    ->setApnsConfig([
        'title' => 'iOS Title',
        'body' => 'iOS Description.',
        'title_loc_key' => 'iOS Title loc key.',
        'badge' => '42',
        'sound' => 'bingbong.aiff',
    ])
    ->setWebPushConfig([
        'title' => 'Web push Title',
        'body' => 'Web push Description.',
        'icon' => 'https://my-server/icon.png',
        'click_action' => 'click-action',
    ])
    ->setCollapseKey('collapse_key')
    ->setPriority(\aksafan\fcm\source\helpers\OptionsHelper::NORMAL)
    ->setContentAvailable(true)
    ->setMutableContent(false)
    ->setTimeToLive(300)
    ->setRestrictedPackageName('restricted_package_mame')
    ->validateOnly(false)
    ->send();
```

> **_N.B._** _Pay attention that platform specific configuration will replace the general one and repeating from other platform configurations (shortage in legacy API version), one by one in order:_
```$xslt
    GeneralNotificationConfig
         ->
    AndroidConfig
         ->
    ApnsConfig
         ->
    WebPushConfig
```
In example above any client will receive this notification info: 
```php
'title' => 'Web push Title',
'body' => 'Web push Description.',
'icon' => 'https://my-server/icon.png',
'color' => '#ff0000',
'title_loc_key' => 'iOS Title loc key.',
'badge' => '42',
'sound' => 'bingbong.aiff',
'click_action' => 'click-action',
```
and **NOT** all mentioned.

##### Handling response.

After sending the request to FCM you will get an instance of these:
- `\aksafan\fcm\responses\legacyApi\TokenResponse`
- `\aksafan\fcm\responses\legacyApi\TopicResponse`
- `\aksafan\fcm\responses\legacyApi\GroupResponse`

###### For sending push-messages to single (multiply) token(s):
```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => '2'])
    ->setNotification('Test title', 'Test description')
    ->send();
    
if ($result->isResultOk()) {
    echo 'MessageId '.$result->getMessageId();
    echo 'NumberSuccess '.$result->getNumberSuccess();
    echo 'NumberFailure '.$result->getNumberFailure();
    echo 'NumberModification '.$result->getNumberModification();
} else {
    echo 'numberSuccess '.$result->getNumberSuccess();
    echo 'numberFailure '.$result->getNumberFailure();
    echo 'numberModification '.$result->getNumberModification();
    echo 'TokensToDelete '.$result->getTokensToDelete();
    echo 'TokensToModify '.$result->getTokensToModify();
    echo 'TokensToRetry '.$result->getTokensToRetry();
    echo 'RetryAfter '.$result->getRetryAfter();
    echo 'TokensWithError '.$result->getTokensWithError();
    echo 'ErrorStatusDescription '.$result->getErrorStatusDescription();
}
```
If the result is OK you can get the message ID in format - `message_id`:
```php
$result->getMessageId();
```
You can see the number of successfully sent messages:
```php
$result->getNumberSuccess();
```
The number of failed attempts:
```php
$result->getNumberFailure();
```
The number of device that you need to modify their token:
```php
$result->getNumberModification();
```
If something has happened, you can get error description with the information about the problem:
```php
$result->getErrorStatusDescription();
```
> List of common errors and info about handling them for legacy API is [here](https://firebase.google.com/docs/cloud-messaging/http-server-ref#table9)

Also tokens, that should be deleted from your DB as invalid ones:
```php
$result->getTokensToDelete();
```
Tokens, that should be changed in your storage - `['oldToken' => 'newToken']`:
```php
$result->getTokensToModify();
```
Tokens, that should be resend. You should to use [exponential backoff](https://en.wikipedia.org/wiki/Exponential_backoff) to retry sending:
```php
$result->getTokensToRetry();
```
Time to retry after if it was in response headers:
```php
$result->getRetryAfter();
```
Tokens, that have errors. You should check these tokens in order to delete broken ones:
```php
$result->getTokensWithError();
```

###### For sending push-messages to topic or topics by condition:
```php
/** @var \aksafan\fcm\responses\legacyApi\TopicResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_SENDING)
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOPIC, 'a-topic')
    ->setNotification('Test title', 'Test description')
    ->send();
    
if ($result->isResultOk()) {
    echo 'MessageId '.$result->getMessageId();
} else {
    echo 'ErrorMessage '.$result->getErrorMessage();
    echo 'ErrorStatusDescription '.$result->getErrorStatusDescription();
}
```
If the result is OK you can get the message ID in format - `message_id`:
```php
$result->getMessageId();
```
If something has happened, you can get error description with the information about the problem:
```php
$result->getErrorStatusDescription();
```
And error message:
```php
$result->getErrorMessage();
```


###### For sending push-messages to device group:
```php
$notificationKey = 'your_notification_key';
/** @var \aksafan\fcm\responses\legacyApi\GroupResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_SENDING)
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::GROUP, $notificationKey)
    ->setData(['a' => '1', 'b' => '2'])
    ->setNotification('Test title', 'Test description')
    ->send();
    
if ($result->isResultOk()) {
    echo 'numberSuccess ' . $result->getNumberSuccess();
    echo 'numberFailure ' . $result->getNumberFailure();
} else {
    echo 'numberSuccess ' . $result->getNumberSuccess();
    echo 'numberFailure ' . $result->getNumberFailure();
    echo 'tokensFailed ' . print_r($tokensFailed = $result->getTokensFailed());
    echo 'ErrorStatusDescription '.$result->getErrorStatusDescription();
}
```
If the result is OK you can get the number of tokens successfully sent messages to:
```php
$result->getNumberSuccess();
```
The number of tokens unsuccessfully sent messages to:
```php
$result->getNumberFailure();
```
> If the server attempts to send a message to a device group that has no members, the response will be with 0 success and 0 failure.

If something has happened, you can get error description with the information about the problem:
```php
$result->getErrorStatusDescription();
```
And an array of tokens failed:
```php
$result->getTokensFailed();
```
> When a message fails to be delivered to one or more of the registration tokens associated with a notification_key, the app server should retry with backoff between retries. 


##### Validating messages ([dry run mode](https://firebase.google.com/docs/cloud-messaging/admin/send-messages#sending_in_the_dry_run_mode)).

You can validate a message by sending a validation-only request to the Firebase REST API.
```php
/** @var \aksafan\fcm\responses\legacyApi\TokenResponse $result */
$result = $fcm
    ->createRequest()
    ->setTarget(\aksafan\fcm\builders\legacyApi\MessageOptionsBuilder::TOKEN, $token)
    ->setData(['a' => '1', 'b' => 'test'])
    ->setNotification('Validating messages', 'Test Description')
    ->validateOnly()
    ->send();
    
if ($result->isResultOk()) {
    echo $result->getMessageId();
} else {
    echo $result->getErrorStatusDescription();
}
```
If the message is invalid, you will receive info in error description:
```php
$result->getErrorStatusDescription()
```
If it is valid, you will get fake_message - `-1`:
```php
$result->getMessageId();
```

##### Device group [management](https://firebase.google.com/docs/cloud-messaging/android/device-group#managing_device_groups).

There are three main definitions here:
- `groupName` - is a name or identifier (e.g., it can be a username) that is unique to a given group;
- `notificationKey` - identifies the device group by mapping a particular group (typically a user) to all of the group's associated registration tokens;
- `tokens` - an array of registration tokens for each device you want to add to the group.

> The `groupName` and `notificationKey` are unique to a group of registration tokens. It is important that `groupName` is unique per client app if you have multiple client apps for the same sender ID. This ensures that messages only go to the intended target app. 

> Optionally, Android client apps can manage device groups from the client side. 

###### Create group:
To create a device group, you need to use `createGroup(string $groupName, array $tokens)` method with a name for the group and a list of registration tokens for the devices.
Also creating request with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT` argument.
```php
$groupName = 'test-group';
$tokens = [
    'your_token_1',
    'your_token_2',
    'your_token_3',
    'your_token_4',
    'your_token_5',
];
/** @var \aksafan\fcm\responses\legacyApi\GroupManagementResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT)
    ->createGroup($groupName, $tokens)
    ->send();
if ($result->isResultOk()) {
    echo 'NotificationKey '.$notificationKey = $result->getNotificationKey();
} else {
    echo 'getErrorStatusDescription '. $result->getErrorStatusDescription();
}
```
After sending the request to FCM you will get an instance of `\aksafan\fcm\responses\legacyApi\GroupManagementResponse`.

FCM returns a new `notificationKey` that represents the device group. Save it and the corresponding `groupName` to use in subsequent operations. 

If there was an error, you will receive info in error description:
```php
$result->getErrorStatusDescription()
```
> **_N.B._** _You can add to group up to 1,000 devices in a single request. If you provide an array with over 1,000 registration tokens, the request will fail with an `InvalidArgumentException`._ 

###### Retrieve `notificationKey` from a group:
If you need to retrieve an existing `notificationKey`, you need to use `getNotificationKey(string $groupName)` method with a name for the group.
Also creating request with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT` argument.
```php
$groupName = 'test-group';
/** @var \aksafan\fcm\responses\legacyApi\GroupManagementResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT)
    ->getNotificationKey($groupName)
    ->sendGET();
if ($result->isResultOk()) {
    echo 'NotificationKey '.$notificationKey = $result->getNotificationKey();
    echo '<br>';
} else {
    echo 'getErrorStatusDescription '. $result->getErrorStatusDescription();
    echo '<br>';
}
```
After sending the request to FCM you will get an instance of `\aksafan\fcm\responses\legacyApi\GroupManagementResponse`.

FCM returns the `notificationKey` that represents the device group.

If there was an error, you will receive info in error description:
```php
$result->getErrorStatusDescription()
```

> __Note__: Notification_key_name is not required for adding/removing registration tokens, but including it protects you against accidentally using the incorrect notification_key. In order to be safe, current extension make you use `groupName` always. 

###### Add token(s) to group:

You can add tokens to device group passing registration tokens to the `addToGroup(string $groupName, string $notificationKey, array $tokens)` method and creating request with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT` argument:
```php
$groupName = 'test-group';
$tokens = [
    'your_token_6',
    'your_token_7',
    'your_token_8',
    'your_token_9',
    'your_token_10',
];
/** @var \aksafan\fcm\responses\legacyApi\GroupManagementResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT)
    ->addToGroup($groupName, $notificationKey, $tokens)
    ->send();
if ($result->isResultOk()) {
    echo 'NotificationKey '.$result->getNotificationKey();
} else {
    echo 'getErrorStatusDescription '. $result->getErrorStatusDescription();
}
```
After sending the request to FCM you will get an instance of `\aksafan\fcm\responses\legacyApi\GroupManagementResponse`.

FCM returns a new `notificationKey` that represents the device group.

If there was an error, you will receive info in error description:
```php
$result->getErrorStatusDescription()
```

###### Remove token(s) from group:

You can add tokens to device group passing registration tokens to the `removeFromGroup(string $groupName, string $notificationKey, array $tokens)` method and creating request with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT` argument:
```php
$groupName = 'test-group';
$tokens = [
    'your_token_6',
    'your_token_7',
];
/** @var \aksafan\fcm\responses\legacyApi\GroupManagementResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_GROUP_MANAGEMENT)
    ->removeFromGroup($groupName, $notificationKey, $tokens)
    ->send();
if ($result->isResultOk()) {
    echo 'NotificationKey '.$result->getNotificationKey();
    echo '<br>';
} else {
    echo 'getErrorStatusDescription '. $result->getErrorStatusDescription();
    echo '<br>';
}
```
After sending the request to FCM you will get an instance of `\aksafan\fcm\responses\legacyApi\GroupManagementResponse`.

FCM returns a new `notificationKey` that represents the device group.

If there was an error, you will receive info in error description:
```php
$result->getErrorStatusDescription()
```

> If you remove all existing registration tokens from a device group, FCM deletes the device group.


#### Topic [management](https://firebase.google.com/docs/cloud-messaging/admin/manage-topic-subscriptions).

> __Note__: You can (un)subscribe to (from) topic through both API versions, just chose your favorite and [configure](#Configuration) it properly.

##### [Subscribe](https://firebase.google.com/docs/cloud-messaging/admin/manage-topic-subscriptions#subscribe_to_a_topic) to a topic:

You can subscribe one or multiple devices to a topic by passing registration tokens to the `subscribeToTopic(string $topic, array $tokens)` method and creating request with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_MANAGEMENT` argument:
```php
$topic = 'test-topic';
$tokens = [
    'your_token_1',
    'your_token_2',
    'your_token_3',
    'your_token_4',
    'your_token_5',
];
/** @var \aksafan\fcm\responses\TopicSubscribeResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_MANAGEMENT)
    ->subscribeToTopic($topic, $tokens)
    ->send();
```

> **_N.B._** _You can subscribe up to 1,000 devices in a single request. If you provide an array with over 1,000 registration tokens, the request will fail with an `InvalidArgumentException`._ 

After sending the request to FCM you will get an instance of `\aksafan\fcm\responses\TopicSubscribeResponse`.
Now you can grab tokens with [errors](https://firebase.google.com/docs/cloud-messaging/admin/errors):
```php
$tokensWithError = $result->getTopicTokensWithError();
```
This is an array of tokens with their correspondents errors in format:
```php
$tokensWithError = [
    [
        'token' => 'your_token_2',
        'error' => 'INVALID_ARGUMENT',
    ],
    [
        'token' => 'your_token_4',
        'error' => 'INVALID_ARGUMENT',
    ],
    [
        'token' => 'your_token_5',
        'error' => 'INVALID_ARGUMENT',
    ],
];
```
All other tokens were subscribed correctly.

##### [Unsubscribe](https://firebase.google.com/docs/cloud-messaging/admin/manage-topic-subscriptions#unsubscribe_from_a_topic) from a topic:

You can unubscribe one or multiple devices to a topic by passing registration tokens to the `unsubscribeFromTopic(string $topic, array $tokens)` method and creating request with `\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_MANAGEMENT` argument:
```php
$topic = 'test-topic';
$tokens = [
    'your_token_1',
    'your_token_2',
    'your_token_3',
    'your_token_4',
    'your_token_5',
];
/** @var \aksafan\fcm\responses\TopicSubscribeResponse $result */
$result = $fcm
    ->createRequest(\aksafan\fcm\builders\StaticBuilderFactory::FOR_TOPIC_MANAGEMENT)
    ->unsubscribeFromTopic($topic, $tokens)
    ->send();
```

> **_N.B._** _You can unsubscribe up to 1,000 devices in a single request. If you provide an array with over 1,000 registration tokens, the request will fail with a messaging/invalid-argument error._ 

After sending the request to FCM you will get an instance of `\aksafan\fcm\responses\TopicSubscribeResponse`.
Now you can grab tokens with [errors](https://firebase.google.com/docs/cloud-messaging/admin/errors):
```php
$tokensWithError = $result->getTopicTokensWithError();
```
This is an array of tokens with their correspondents errors in format:
```php
$tokensWithError = [
    [
        'token' => 'your_token_2',
        'error' => 'INVALID_ARGUMENT',
    ],
    [
        'token' => 'your_token_4',
        'error' => 'INVALID_ARGUMENT',
    ],
    [
        'token' => 'your_token_5',
        'error' => 'INVALID_ARGUMENT',
    ],
];
```
All other tokens were unsubscribed correctly.

#### Logging

This extension uses native Yii2 error logger through `\Yii::error()`.
List of used error categories can be found here `aksafan\fcm\helpers\ErrorsHelper::LOGS_ERRORS`.


License
-------

Copyright 2018 by Anton Khainak.

Available under the MIT license.

Complete FCM documentation can be found here [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging/).
