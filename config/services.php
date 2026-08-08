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

    'cessa_api' => [
        'url' => env('CESSA_API_URL'),
        'token' => env('CESSA_API_TOKEN'),
    ],

    'contact' => [
        'notify_email' => env('CONTACT_NOTIFICATION_EMAIL', 'cloudflare.dtic@cessa.com.bo'),
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'sip' => [
        'base_url' => env('SIP_BASE_URL', 'https://dev-sip.mc4.com.bo:8443'),
        'apikey' => env('SIP_QR_API_KEY'),
        'username' => env('SIP_USERNAME'),
        'password' => env('SIP_PASSWORD'),
        'apikey_servicio' => env('SIP_SERVICE_API_KEY'),
        'callback_username' => env('SIP_CALLBACK_USERNAME'),
        'callback_password' => env('SIP_CALLBACK_PASSWORD'),
    ],

    'sms' => [
        // 'log' (por defecto, solo escribe al log) hasta tener la API real de Tigo.
        'provider' => env('SMS_PROVIDER', 'log'),
    ],

];
