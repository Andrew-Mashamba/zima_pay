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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'payment_gateway' => [
        'base_url' => env('PAYMENT_GATEWAY_BASE_URL', 'https://zimapay.co.tz'),
        'api_key' => env('PAYMENT_GATEWAY_API_KEY', 'sample_client_key_ABC123DEF456'),
        'api_secret' => env('PAYMENT_GATEWAY_API_SECRET', 'sample_client_secret_XYZ789GHI012'),
        'frontend_url' => env('PAYMENT_GATEWAY_FRONTEND_URL', env('PAYMENT_GATEWAY_BASE_URL', 'https://zimapay.co.tz')),
        'url' => env('PAYMENT_LINK_API_URL', 'https://zimapay.co.tz/api/payment-links/generate-universal'),
    ],

];
