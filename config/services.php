<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    // 'static_qris' => [
    //     'price' => (int) env('PHOTOBOOTH_PRICE', 12000),
    //     'webhook_token' => env('STATIC_QRIS_WEBHOOK_TOKEN'),
    //     'image_url' => '/images/qris-gopay.jpeg', // Simpan gambar QRIS statis di public/images/qris-gopay.png
    // ],
    'static_qris' => [
        'price' => (int) env('PHOTOBOOTH_PRICE', 100),
        'webhook_token' => env('STATIC_QRIS_WEBHOOK_TOKEN'),
        'raw_string' => env('STATIC_QRIS_STRING'),
    ],
];
