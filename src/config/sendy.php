<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sendy Installation URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Sendy installation, without trailing slash
    | Example: https://your-sendy-installation.com
    |
    */
    'url' => env('SENDY_URL'),

    /*
    |--------------------------------------------------------------------------
    | Sendy API Key
    |--------------------------------------------------------------------------
    |
    | Your Sendy installation API key
    |
    */
    'api_key' => env('SENDY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sendy List ID
    |--------------------------------------------------------------------------
    |
    | The ID of the list you want to subscribe users to
    |
    */
    'list_id' => env('SENDY_LIST_ID'),

    /*
    |--------------------------------------------------------------------------
    | Target Table
    |--------------------------------------------------------------------------
    |
    | The database table containing users to sync with Sendy
    | Default: users
    |
    */
    'target_table' => env('SENDY_TARGET_TABLE', 'users'),

    /*
    |--------------------------------------------------------------------------
    | Sync Interval
    |--------------------------------------------------------------------------
    |
    | How often should the sync command run in minutes
    | Default: 60 (hourly)
    |
    */
    'sync_interval' => env('SENDY_SYNC_INTERVAL', 60),

    /*
    |--------------------------------------------------------------------------
    | GDPR Compliance
    |--------------------------------------------------------------------------
    |
    | Set to true if you're signing up EU users in a GDPR compliant manner
    | Default: false
    |
    */
    'gdpr' => env('SENDY_GDPR', false),

    /*
    |--------------------------------------------------------------------------
    | Silent Mode
    |--------------------------------------------------------------------------
    |
    | Set to true to bypass Double opt-in and signup users as Single Opt-in
    | Default: true
    |
    */
    'silent' => env('SENDY_SILENT', true),

    /*
    |--------------------------------------------------------------------------
    | Referrer URL
    |--------------------------------------------------------------------------
    |
    | The URL where users are being signed up from
    | Defaults to your Laravel application URL
    |
    */
    'referrer' => env('SENDY_REFERRER', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | Honeypot Protection
    |--------------------------------------------------------------------------
    |
    | Enable honeypot field to prevent spambots (Sendy 3.0+ only)
    | Default: false
    |
    */
    'honeypot' => env('SENDY_HONEYPOT', false),

    /*
    |--------------------------------------------------------------------------
    | User Fields Mapping
    |--------------------------------------------------------------------------
    |
    | Map your user table fields to Sendy fields.
    | 'email' field is required.
    | 'name' is an optional standard field.
    | 'country' should be a 2-letter country code.
    | 'ipaddress' for user's IP address.
    | Any other fields will be sent as custom fields to Sendy.
    | Make sure to create the custom fields in your Sendy installation first.
    |
    */
    'fields_mapping' => [
        // Required field
        'email' => 'email',

        // Optional standard fields
        'name' => 'name',
        'country' => 'country_code',    // Should be 2-letter country code
        'ipaddress' => 'ip_address',    // User's IP address

        // Example custom fields (uncomment and adjust as needed)
        // 'Birthday' => 'birth_date',
        // 'Company' => 'organization',
        // 'Phone' => 'contact_number',
    ],
];
