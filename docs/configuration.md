# Configuration

Laravel TurboMaker can be configured to match your project's structure and preferences. This guide covers all available configuration options.

## Publishing Configuration

To customize TurboMaker's behavior, publish the configuration file:

```bash
php artisan vendor:publish --tag=turbomaker-config
```

This creates `config/turbomaker.php` in your project.

## Configuration File Structure

```php
<?php

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
```

## Configuration Options Explained

### Default Generation Options

Control what components are generated by default:

```php
'defaults' => [
    'generate_tests' => true,           // Generate tests automatically
    'generate_factory' => true,        // Generate model factories
    'generate_seeder' => false,        // Generate seeders
    'generate_policies' => false,      // Generate authorization policies
    'generate_api_resources' => true,  // Generate API resources
    'generate_actions' => false,       // Generate action classes
    'generate_services' => false,      // Generate service classes
    'generate_rules' => false,         // Generate validation rules
    'generate_observers' => false,     // Generate model observers
    'format' => 'table',               // Default output format
    'include_metadata' => true,        // Include metadata in output
    'show_progress' => true,           // Show progress indicators
],
```

### File Paths

Customize where different types of files are generated:

```php
'paths' => [
    'models' => 'app/Models',                    // Eloquent models
    'controllers' => 'app/Http/Controllers',     // Web controllers
    'api_controllers' => 'app/Http/Controllers/Api', // API controllers
    'requests' => 'app/Http/Requests',          // Form request classes
    'resources' => 'app/Http/Resources',        // API resource classes
    'policies' => 'app/Policies',               // Authorization policies
    'actions' => 'app/Actions',                 // Action classes
    'services' => 'app/Services',               // Service classes
    'rules' => 'app/Rules',                     // Validation rules
    'observers' => 'app/Observers',             // Model observers
    'migrations' => 'database/migrations',      // Database migrations
    'factories' => 'database/factories',        // Model factories
    'seeders' => 'database/seeders',           // Database seeders
    'views' => 'resources/views',              // Blade views
    'tests' => 'tests',                        // Test directory
    'feature_tests' => 'tests/Feature',        // Feature tests
    'unit_tests' => 'tests/Unit',              // Unit tests
],
```

### Custom Namespaces

For projects with different namespace structures:

```php
'paths' => [
    'models' => 'src/Domain/Models',
    'controllers' => 'src/Http/Controllers',
    'services' => 'src/Application/Services',
],
```

### Stub Templates

Configure which stub templates to use:

```php
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
```

### Status Tracking

Control logging and status tracking:

```php
'status_tracking' => [
    'enabled' => true,
    'log_file' => storage_path('logs/turbomaker.log'),
],
```

### Performance Settings

Configure performance and caching:

```php
'performance' => [
    'cache_enabled' => true,
    'cache_ttl' => 3600,              // 1 hour cache
    'memory_limit' => '512M',
],
```

### Scanner Configuration

Configure different scanners and analyzers:

```php
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
```

## Environment-Specific Configuration

You can override configuration values in different environments:

**config/turbomaker.php:**
```php
return [
    'defaults' => [
        'generate_tests' => env('TURBOMAKER_GENERATE_TESTS', false),
        'generate_factory' => env('TURBOMAKER_GENERATE_FACTORY', false),
    ],
    
    'code_style' => [
        'auto_format' => env('TURBOMAKER_AUTO_FORMAT', false),
    ],
];
```

**.env:**
```bash
# Development environment
TURBOMAKER_GENERATE_TESTS=true
TURBOMAKER_GENERATE_FACTORY=true
TURBOMAKER_AUTO_FORMAT=true
```

**.env.production:**
```bash
# Production environment
TURBOMAKER_GENERATE_TESTS=false
TURBOMAKER_GENERATE_FACTORY=false
TURBOMAKER_AUTO_FORMAT=false
```

## Team Configuration

For team projects, consider these settings:

```php
return [
    // Consistent file generation
    'defaults' => [
        'generate_tests' => true,
        'generate_factory' => true,
        'generate_policies' => true,
    ],
    
    // Enforce code standards
    'code_style' => [
        'strict_types' => true,
        'docblocks' => true,
        'auto_format' => true,
    ],
    
    // Consistent database structure
    'database' => [
        'use_foreign_key_constraints' => true,
        'cascade_on_delete' => false, // Be explicit about cascades
    ],
];
```

## Advanced Configuration

### Custom Stub Path per Template

```php
'stubs' => [
    'templates' => [
        'model' => resource_path('stubs/custom/model.stub'),
        'controller' => resource_path('stubs/shared/controller.stub'),
        // Other templates use default path
    ],
],
```

### Conditional Configuration

```php
'model' => [
    'default_traits' => app()->environment('testing') ? [] : [
        'App\\Traits\\Auditable',
        'App\\Traits\\Cacheable',
    ],
],
```

### Multiple Environments

```php
'testing' => [
    'framework' => app()->environment(['local', 'testing']) ? 'pest' : 'phpunit',
    'use_database_transactions' => !app()->environment('production'),
],
```

## Configuration Validation

TurboMaker validates configuration on startup. Common validation errors:

### Invalid Paths
```bash
# Error: Path does not exist
'paths' => [
    'models' => 'invalid/path', // This will cause an error
],
```

### Missing Stub Files
```bash
# Error: Stub file not found
'stubs' => [
    'templates' => [
        'model' => 'missing.stub', // This will cause an error
    ],
],
```

### Invalid Namespaces
```bash
# Error: Invalid namespace format
'model' => [
    'namespace' => 'Invalid\\Namespace\\Format\\', // Trailing slash
],
```

## Best Practices

### 1. Version Control Configuration
Always commit your configuration file to version control.

### 2. Environment-Specific Settings
Use environment variables for settings that vary between environments.

### 3. Team Standards
Establish team standards for code generation and document them.

### 4. Gradual Adoption
Start with default settings and gradually customize as needed.

### 5. Test Configuration
Test your configuration with sample modules before using in production.

### 6. Backup Before Changes
Always backup your configuration before making significant changes.

## Troubleshooting

### Configuration Not Loading
1. Clear configuration cache: `php artisan config:clear`
2. Ensure file is properly published
3. Check file permissions

### Generation Errors
1. Verify all paths exist and are writable
2. Check stub file paths are correct
3. Validate namespace formats

### Performance Issues
1. Disable auto-formatting during development
2. Reduce default component generation
3. Use specific commands instead of `turbo:make` with all options

## Examples

See the [examples/configuration](../examples/configuration/) directory for:
- Complete configuration examples
- Team-specific configurations
- Environment-specific setups
- Performance-optimized configurations