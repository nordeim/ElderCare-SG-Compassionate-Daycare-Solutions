<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sentry Laravel configuration
    |--------------------------------------------------------------------------
    |
    | This file returns a small subset of Sentry options used by the
    | application. The package will be registered conditionally in
    | the AppServiceProvider when a DSN is present.
    |
    */

    'dsn' => env('SENTRY_LARAVEL_DSN', ''),

    'release' => env('SENTRY_RELEASE', null),

    'environment' => env('APP_ENV', 'production'),

    'breadcrumbs' => [
        'sql_bindings' => true,
    ],

    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
];
