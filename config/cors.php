<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', '*'))),

    // Regex patterns for origins that can't be listed as fixed strings above —
    // e.g. `flutter run -d chrome` binds Flutter Web's dev server to a random
    // port each run, so a fixed CORS_ALLOWED_ORIGINS entry can't cover it.
    'allowed_origins_patterns' => array_filter(explode(',', env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
