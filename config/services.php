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

    'n8n' => [
        'token' => env('N8N_API_TOKEN'),
    ],

    // Rótulos de costales de salida de MP (SLI de SGP). La IP se toma de
    // sgpforma.dbo.ini_impresoras por el nombre, igual que hace SGP.
    'rotulos_mp' => [
        'impresora'     => env('ROTULOS_MP_IMPRESORA', 'ZTC-230-ZPL-INV'),
        'puerto'        => (int) env('ROTULOS_MP_PUERTO', 9100),
        'max_etiquetas' => (int) env('ROTULOS_MP_MAX_ETIQUETAS', 100),
    ],

];
