<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sendy Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Sendy integration.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Sendy URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Sendy installation.
    |
    */
    'url' => env('SENDY_URL', 'http://your-sendy-installation.com'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Sendy API key.
    |
    */
    'api_key' => env('SENDY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | List ID
    |--------------------------------------------------------------------------
    |
    | The ID of the list you want to subscribe users to.
    |
    */
    'list_id' => env('SENDY_LIST_ID'),

    /*
    |--------------------------------------------------------------------------
    | Target Table
    |--------------------------------------------------------------------------
    |
    | The database table that contains the users to be synced with Sendy.
    |
    */
    'target_table' => env('SENDY_TARGET_TABLE', 'users'),

    /*
    |--------------------------------------------------------------------------
    | Sync Interval
    |--------------------------------------------------------------------------
    |
    | The interval in minutes between each sync operation.
    |
    */
    'sync_interval' => env('SENDY_SYNC_INTERVAL', 60),

    /*
    |--------------------------------------------------------------------------
    | GDPR Compliance
    |--------------------------------------------------------------------------
    |
    | Whether to enable GDPR compliance mode.
    |
    */
    'gdpr' => env('SENDY_GDPR', false),

    /*
    |--------------------------------------------------------------------------
    | Silent Mode
    |--------------------------------------------------------------------------
    |
    | Whether to suppress error messages and continue processing on errors.
    |
    */
    'silent' => env('SENDY_SILENT', true),

    /*
    |--------------------------------------------------------------------------
    | Referrer URL
    |--------------------------------------------------------------------------
    |
    | The referrer URL to be sent with subscription requests.
    |
    */
    'referrer' => env('SENDY_REFERRER', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | Honeypot Protection
    |--------------------------------------------------------------------------
    |
    | Whether to enable honeypot protection for subscription forms.
    |
    */
    'honeypot' => env('SENDY_HONEYPOT', false),
];
