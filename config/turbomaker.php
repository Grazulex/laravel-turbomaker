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
        'generate_tests' => env('TURBOMAKER_GENERATE_TESTS', true),
        'generate_factory' => env('TURBOMAKER_GENERATE_FACTORY', true),
        'generate_seeder' => env('TURBOMAKER_GENERATE_SEEDER', false),
        'generate_policies' => env('TURBOMAKER_GENERATE_POLICIES', false),
        'generate_api_resources' => env('TURBOMAKER_GENERATE_API_RESOURCES', true),
        'generate_actions' => env('TURBOMAKER_GENERATE_ACTIONS', false),
        'generate_services' => env('TURBOMAKER_GENERATE_SERVICES', false),
        'generate_rules' => env('TURBOMAKER_GENERATE_RULES', false),
        'generate_observers' => env('TURBOMAKER_GENERATE_OBSERVERS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Paths
    |--------------------------------------------------------------------------
    |
    | Configure where different types of files should be generated.
    |
    */
    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers',
        'api_controllers' => 'app/Http/Controllers/Api',
        'requests' => 'app/Http/Requests',
        'resources' => 'app/Http/Resources',
        'policies' => 'app/Policies',
        'actions' => 'app/Actions',
        'services' => 'app/Services',
        'rules' => 'app/Rules',
        'observers' => 'app/Observers',
        'migrations' => 'database/migrations',
        'factories' => 'database/factories',
        'seeders' => 'database/seeders',
        'views' => 'resources/views',
        'tests' => 'tests',
        'feature_tests' => 'tests/Feature',
        'unit_tests' => 'tests/Unit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub Templates
    |--------------------------------------------------------------------------
    |
    | Configure which stub templates to use for code generation.
    | TurboMaker will first look for published stubs in your project,
    | then fallback to the package default stubs.
    |
    | To customize stubs, run: php artisan vendor:publish --tag=turbomaker-stubs
    |
    */
    'stubs' => [
        'path' => resource_path('stubs/turbomaker'),
        'templates' => [
            'model' => 'model.stub',
            'controller' => 'controller.stub',
            'api_controller' => 'controller.api.stub',
            'migration' => 'migration.stub',
            'factory' => 'factory.stub',
            'seeder' => 'seeder.stub',
            'policy' => 'policy.stub',
            'request_store' => 'request.store.stub',
            'request_update' => 'request.update.stub',
            'resource' => 'resource.stub',
            'action_create' => 'action.create.stub',
            'action_update' => 'action.update.stub',
            'action_delete' => 'action.delete.stub',
            'action_get' => 'action.get.stub',
            'service' => 'service.stub',
            'rule_exists' => 'rule.exists.stub',
            'rule_unique' => 'rule.unique.stub',
            'observer' => 'observer.stub',
            'test_feature' => 'test.feature.stub',
            'test_unit' => 'test.unit.stub',
            'view_index' => 'view.index.stub',
            'view_create' => 'view.create.stub',
            'view_edit' => 'view.edit.stub',
            'view_show' => 'view.show.stub',
        ],
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
    | Schema Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for schema-based generation using YAML files.
    |
    */
    'schemas' => [
        'path' => env('TURBOMAKER_SCHEMAS_PATH', resource_path('schemas')),
        'extension' => '.schema.yml',
        'auto_discovery' => env('TURBOMAKER_SCHEMA_AUTO_DISCOVERY', true),
        'cache_enabled' => env('TURBOMAKER_SCHEMA_CACHE', true),
        'validate_on_load' => env('TURBOMAKER_SCHEMA_VALIDATE', true),
        'backup_on_generate' => env('TURBOMAKER_SCHEMA_BACKUP', false),
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

    /*
    |--------------------------------------------------------------------------
    | Custom Field Types
    |--------------------------------------------------------------------------
    |
    | Register custom field types that extend the built-in functionality.
    | Each entry should map a type name to a class that implements
    | FieldTypeInterface.
    |
    | Example:
    | 'custom_field_types' => [
    |     'money' => App\TurboMaker\FieldTypes\MoneyFieldType::class,
    |     'slug' => App\TurboMaker\FieldTypes\SlugFieldType::class,
    | ],
    |
    */
    'custom_field_types' => [
        // Add your custom field types here
    ],
];
