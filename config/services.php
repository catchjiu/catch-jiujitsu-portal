<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    /*
    | LINE Messaging API (push messages from your LINE Official Account / bot)
    | Create a channel at https://developers.line.biz/ → Messaging API.
    | Set webhook URL to: APP_URL/webhook/line
    | line_id on users = LINE user ID (from webhook follow/message events).
    */
    'line_messaging' => [
        'channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN'),
        'channel_secret' => env('LINE_CHANNEL_SECRET'),
        'add_friend_url' => env('LINE_ADD_FRIEND_URL'), // e.g. https://line.me/R/ti/p/@yourbot
    ],

    /*
    | LIFF (LINE Front-end Framework) – open links from LINE in-app browser and log in by line_id.
    | LINE no longer allows adding LIFF to Messaging API channels. Use a LINE Login channel:
    | create a LINE Login channel, add your LIFF app there (Endpoint URL = APP_URL/liff, permanent link "concat").
    | Link that LINE Login channel to your Messaging API bot so the same user has the same ID (line_id).
    | Channel ID below is the LINE Login channel's Channel ID (for Verify ID token API).
    */
    'liff' => [
        'liff_id' => env('LINE_LIFF_ID'),
        'channel_id' => env('LINE_CHANNEL_ID'), // LINE Login channel ID (for Verify ID token API)
    ],

];
