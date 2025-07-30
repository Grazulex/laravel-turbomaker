<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Turbomaker Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for Laravel Turbomaker.
    | You can customize the behavior of various scanners and commands here.
    |
    */

    'enabled' => env('TURBOMAKER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | These are the default settings used across all Turbomaker commands.
    |
    */
    'defaults' => [
        'format' => env('TURBOMAKER_DEFAULT_FORMAT', 'table'),
        'include_metadata' => env('TURBOMAKER_INCLUDE_METADATA', true),
        'show_progress' => env('TURBOMAKER_SHOW_PROGRESS', true),
        'output_path' => env('TURBOMAKER_OUTPUT_PATH', storage_path('turbomaker')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Tracking
    |--------------------------------------------------------------------------
    |
    | Enable or disable status tracking for commands and operations.
    |
    */
    'status_tracking' => [
        'enabled' => env('TURBOMAKER_STATUS_TRACKING', true),
        'log_file' => env('TURBOMAKER_LOG_FILE', storage_path('logs/turbomaker.log')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scanner Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different scanners and analyzers.
    |
    */
    'scanners' => [
        'routes' => [
            'include_middleware' => true,
            'show_unused' => true,
        ],
        'models' => [
            'include_relationships' => true,
            'show_attributes' => true,
        ],
        'views' => [
            'check_unused' => true,
            'include_components' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Settings to optimize performance for large applications.
    |
    */
    'performance' => [
        'cache_enabled' => env('TURBOMAKER_CACHE_ENABLED', true),
        'cache_ttl' => env('TURBOMAKER_CACHE_TTL', 3600), // 1 hour
        'memory_limit' => env('TURBOMAKER_MEMORY_LIMIT', '512M'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for exporting scan results to various formats.
    |
    */
    'export' => [
        'formats' => ['json', 'csv', 'html'],
        'include_timestamps' => true,
        'compress_output' => false,
    ],
];
