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

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
    ],

    'africastalking' => [
        'username' => env('AT_USERNAME'),
        'api_key' => env('AT_API_KEY'),
    ],

    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'twilio'),

        'use_templates' => env('WHATSAPP_USE_TEMPLATES', true),

        'templates' => [
            'user_invitation' => env('WHATSAPP_TEMPLATE_USER_INVITATION', 'hello_world'),
            'task_assigned' => env('WHATSAPP_TEMPLATE_TASK_ASSIGNED', 'hello_world'),
            'task_rescheduled' => env('WHATSAPP_TEMPLATE_TASK_RESCHEDULED', 'hello_world'),
            'task_status_updated' => env('WHATSAPP_TEMPLATE_TASK_STATUS', 'hello_world'),
            'task_overdue' => env('WHATSAPP_TEMPLATE_TASK_OVERDUE', 'hello_world'),
        ],

        'meta' => [
            'token' => env('META_WHATSAPP_TOKEN'),
            'phone_id' => env('META_WHATSAPP_PHONE_ID'),
            'business_id' => env('META_WHATSAPP_BUSINESS_ID'),
            'version' => env('META_WHATSAPP_VERSION', 'v21.0'),
        ],

        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
        ],

        'vonage' => [
            'api_key' => env('VONAGE_API_KEY'),
            'api_secret' => env('VONAGE_API_SECRET'),
            'from' => env('VONAGE_WHATSAPP_NUMBER'),
        ],

        'ultramsg' => [
            'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
            'token' => env('ULTRAMSG_TOKEN'),
        ],

        'wati' => [
            'api_url' => env('WATI_API_URL'),
            'access_token' => env('WATI_ACCESS_TOKEN'),
        ],

        'whapi' => [
            'api_url' => env('WHAPI_API_URL'),
            'token' => env('WHAPI_TOKEN'),
        ],
    ],

];
