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
    | Sendy Installation URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Sendy installation.
    |
    */
    'url' => env('SENDY_INSTALLATION_URL', 'http://your-sendy-installation.com'),

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
    | Field Mapping
    |--------------------------------------------------------------------------
    |
    | Map Sendy fields to your model fields. The keys are the Sendy field names,
    | and the values are your model's field names.
    |
    */
    'fields_mapping' => [
        'email' => env('SENDY_EMAIL_FIELD', 'email'),  // Required
        'name' => env('SENDY_NAME_FIELD', 'name'),    // Optional
        // Add your custom fields here
        // 'company' => 'organization_name',
        // 'country' => 'user_country',
    ],

    /*
    |--------------------------------------------------------------------------
    | Synchronization Settings
    |--------------------------------------------------------------------------
    */

    // Default model to sync
    'default_model' => env('SENDY_DEFAULT_MODEL', 'App\\Models\\User'),

    // Chunk size for batch operations
    'sync_chunk_size' => env('SENDY_SYNC_CHUNK_SIZE', 100),

    // Sync schedule interval in minutes
    'sync_interval' => env('SENDY_SYNC_INTERVAL', 60),

    // Sync schedule (cron expression)
    'sync_schedule' => env('SENDY_SYNC_SCHEDULE', 'hourly'),

    /*
    |--------------------------------------------------------------------------
    | Deletion Actions
    |--------------------------------------------------------------------------
    |
    | Configure what happens to Sendy subscriptions when models are deleted
    | Options: 'none', 'unsubscribe', 'delete'
    |
    */
    'on_delete_action' => env('SENDY_ON_DELETE_ACTION', 'none'),
    'on_soft_delete_action' => env('SENDY_ON_SOFT_DELETE_ACTION', 'none'),
];
