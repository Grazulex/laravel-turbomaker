# Configuration Examples

This directory contains various configuration examples for different use cases and team setups.

## Available Configurations

### 1. Team Development Configuration

**config/turbomaker-team.php**

Perfect for team development with consistent standards:

```php
<?php

return [
    // Enforce consistent generation for all team members
    'defaults' => [
        'generate_tests' => true,        // Always generate tests
        'generate_factory' => true,      // Always generate factories
        'generate_policies' => true,     // Always generate policies
        'generate_services' => false,    // Optional services
        'generate_actions' => false,     // Optional actions
        'generate_rules' => false,       // Optional validation rules
    ],

    // Consistent file organization
    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers',
        'api_controllers' => 'app/Http/Controllers/Api',
        'services' => 'app/Services',
        'actions' => 'app/Actions',
        'policies' => 'app/Policies',
        'tests' => 'tests',
        'feature_tests' => 'tests/Feature',
        'unit_tests' => 'tests/Unit',
    ],

    // Team coding standards
    'model' => [
        'namespace' => 'App\\Models',
        'use_timestamps' => true,
        'use_soft_deletes' => false,     // Explicit about soft deletes
        'use_uuids' => false,           // Use auto-increment IDs
        'default_traits' => [],         // No default traits
    ],

    // Consistent database structure
    'database' => [
        'use_foreign_key_constraints' => true,
        'cascade_on_delete' => false,   // Be explicit about cascades
        'default_string_length' => 255,
    ],

    // Testing standards
    'testing' => [
        'framework' => 'pest',
        'use_database_transactions' => true,
        'generate_factories_for_tests' => true,
    ],

    // Code quality enforcement
    'code_style' => [
        'strict_types' => true,
        'docblocks' => true,
        'auto_format' => env('TURBOMAKER_AUTO_FORMAT', false),
    ],
];
```

### 2. Enterprise Configuration

**config/turbomaker-enterprise.php**

For large-scale enterprise applications:

```php
<?php

return [
    // Enterprise defaults with comprehensive features
    'defaults' => [
        'generate_tests' => true,
        'generate_factory' => true,
        'generate_policies' => true,
        'generate_services' => true,     // Always use service layer
        'generate_actions' => true,      // Action-based architecture
        'generate_rules' => true,        // Custom validation rules
        'generate_observers' => true,    // Event-driven architecture
    ],

    // Enterprise file organization
    'paths' => [
        'models' => 'app/Domain/Models',
        'controllers' => 'app/Http/Controllers',
        'api_controllers' => 'app/Http/Controllers/Api/V1',
        'services' => 'app/Domain/Services',
        'actions' => 'app/Domain/Actions',
        'policies' => 'app/Policies',
        'rules' => 'app/Rules',
        'observers' => 'app/Observers',
    ],

    // Enterprise model configuration
    'model' => [
        'namespace' => 'App\\Domain\\Models',
        'base_class' => 'App\\Domain\\Models\\BaseModel',
        'use_uuids' => true,            // Enterprise prefers UUIDs
        'use_soft_deletes' => true,     // Audit trail requirements
        'default_traits' => [
            'App\\Traits\\Auditable',
            'App\\Traits\\Cacheable',
            'App\\Traits\\Searchable',
        ],
    ],

    // Enterprise database standards
    'database' => [
        'migration_table_prefix' => 'ent_',
        'use_foreign_key_constraints' => true,
        'cascade_on_delete' => false,   // Explicit cascade control
        'default_string_length' => 255,
    ],

    // API configuration for microservices
    'api' => [
        'version_prefix' => 'v1',
        'use_api_resources' => true,
        'generate_collection_resources' => true,
        'pagination_wrapper' => true,
    ],

    // Comprehensive testing
    'testing' => [
        'framework' => 'pest',
        'use_database_transactions' => true,
        'generate_factories_for_tests' => true,
    ],

    // Enterprise code standards
    'code_style' => [
        'strict_types' => true,
        'docblocks' => true,
        'auto_format' => true,
    ],

    // Custom stub templates for enterprise standards
    'stubs' => [
        'path' => resource_path('stubs/enterprise'),
        'templates' => [
            'model' => 'enterprise-model.stub',
            'service' => 'enterprise-service.stub',
            'controller.api' => 'enterprise-api-controller.stub',
        ],
    ],
];
```

### 3. Rapid Prototyping Configuration

**config/turbomaker-prototype.php**

For quick prototyping and MVPs:

```php
<?php

return [
    // Minimal generation for speed
    'defaults' => [
        'generate_tests' => false,       // Skip tests for rapid prototyping
        'generate_factory' => true,      // Keep factories for seeding
        'generate_policies' => false,    // Skip authorization initially
        'generate_services' => false,    // Keep controllers simple
        'generate_actions' => false,     // KISS principle
        'generate_rules' => false,       // Basic validation in requests
    ],

    // Simple file organization
    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers',
        'api_controllers' => 'app/Http/Controllers/Api',
    ],

    // Simple model configuration
    'model' => [
        'namespace' => 'App\\Models',
        'use_timestamps' => true,
        'use_soft_deletes' => false,    // Simplify for prototyping
        'use_uuids' => false,           // Auto-increment is simpler
    ],

    // Simple database structure
    'database' => [
        'use_foreign_key_constraints' => false,  // Avoid constraint issues
        'cascade_on_delete' => true,             // Simplify relationships
    ],

    // Views for quick UI
    'views' => [
        'layout' => 'layouts.app',
        'css_framework' => 'bootstrap',
        'generate_pagination' => true,
    ],

    // Minimal API configuration
    'api' => [
        'use_api_resources' => false,   // Simple array responses
        'pagination_wrapper' => false,
    ],
];
```

### 4. API-First Configuration

**config/turbomaker-api.php**

For API-focused applications:

```php
<?php

return [
    // API-focused defaults
    'defaults' => [
        'generate_api' => true,         // Always generate API
        'generate_views' => false,      // No views by default
        'generate_tests' => true,       // API testing is crucial
        'generate_factory' => true,     // For test data
        'generate_policies' => true,    // API authorization
        'generate_services' => true,    // Business logic separation
    ],

    // API-focused paths
    'paths' => [
        'api_controllers' => 'app/Http/Controllers/Api/V1',
        'resources' => 'app/Http/Resources/V1',
        'services' => 'app/Services',
        'policies' => 'app/Policies',
    ],

    // API model configuration
    'model' => [
        'use_uuids' => true,           // UUIDs for public APIs
        'use_timestamps' => true,
        'default_traits' => [
            'App\\Traits\\ApiSerializable',
        ],
    ],

    // API-optimized routes
    'routes' => [
        'api_prefix' => 'api/v1',
        'api_middleware' => ['api', 'auth:sanctum'],
        'generate_resource_routes' => true,
        'generate_api_routes' => true,
    ],

    // Comprehensive API resources
    'api' => [
        'version_prefix' => 'v1',
        'namespace' => 'App\\Http\\Resources\\V1',
        'use_api_resources' => true,
        'generate_collection_resources' => true,
        'pagination_wrapper' => true,
    ],

    // API testing focus
    'testing' => [
        'framework' => 'pest',
        'feature_test_suffix' => 'ApiTest',
        'generate_factories_for_tests' => true,
    ],
];
```

### 5. Microservices Configuration

**config/turbomaker-microservices.php**

For microservice architecture:

```php
<?php

return [
    // Microservice-focused generation
    'defaults' => [
        'generate_api' => true,
        'generate_views' => false,      // Microservices are API-only
        'generate_tests' => true,
        'generate_factory' => true,
        'generate_policies' => true,
        'generate_services' => true,
        'generate_actions' => true,     // Action-based architecture
        'generate_observers' => true,   // Event-driven
    ],

    // Service-oriented paths
    'paths' => [
        'models' => 'app/Domain/Models',
        'controllers' => 'app/Http/Controllers/Api',
        'services' => 'app/Domain/Services',
        'actions' => 'app/Domain/Actions',
        'events' => 'app/Events',
        'listeners' => 'app/Listeners',
    ],

    // Microservice model configuration
    'model' => [
        'namespace' => 'App\\Domain\\Models',
        'use_uuids' => true,           // Distributed system compatibility
        'use_timestamps' => true,
        'default_traits' => [
            'App\\Traits\\EventSourced',
            'App\\Traits\\Cacheable',
        ],
    ],

    // Event-driven architecture
    'events' => [
        'generate_events' => true,
        'generate_listeners' => true,
        'namespace' => 'App\\Events',
    ],

    // API versioning
    'api' => [
        'version_prefix' => 'v1',
        'namespace' => 'App\\Http\\Controllers\\Api\\V1',
        'use_api_resources' => true,
    ],

    // Microservice testing
    'testing' => [
        'framework' => 'pest',
        'use_database_transactions' => false, // External DB transactions
        'generate_integration_tests' => true,
    ],
];
```

### 6. Environment-Specific Configuration

**.env.local**
```bash
# Local development settings
TURBOMAKER_GENERATE_TESTS=true
TURBOMAKER_GENERATE_FACTORY=true
TURBOMAKER_AUTO_FORMAT=true
TURBOMAKER_USE_UUIDS=false
TURBOMAKER_SOFT_DELETES=false
```

**.env.staging**
```bash
# Staging environment settings
TURBOMAKER_GENERATE_TESTS=true
TURBOMAKER_GENERATE_FACTORY=true
TURBOMAKER_AUTO_FORMAT=false
TURBOMAKER_USE_UUIDS=true
TURBOMAKER_SOFT_DELETES=true
```

**.env.production**
```bash
# Production environment settings (minimal generation)
TURBOMAKER_GENERATE_TESTS=false
TURBOMAKER_GENERATE_FACTORY=false
TURBOMAKER_AUTO_FORMAT=false
TURBOMAKER_USE_UUIDS=true
TURBOMAKER_SOFT_DELETES=true
```

## How to Use These Configurations

### 1. Choose a Configuration

Copy the appropriate configuration to your `config/turbomaker.php`:

```bash
# For team development
cp examples/configuration/team-config.php config/turbomaker.php

# For enterprise
cp examples/configuration/enterprise-config.php config/turbomaker.php

# For API-first
cp examples/configuration/api-config.php config/turbomaker.php
```

### 2. Customize for Your Project

Modify the configuration to match your specific needs:

```php
// Adjust paths for your project structure
'paths' => [
    'models' => 'src/Models',           // Custom src directory
    'controllers' => 'src/Controllers', // Different organization
],

// Set your company standards
'model' => [
    'namespace' => 'YourCompany\\Models',
    'default_traits' => [
        'YourCompany\\Traits\\Auditable',
    ],
],
```

### 3. Test Your Configuration

Generate a test model to verify the configuration:

```bash
php artisan turbo:make TestModel --tests --factory --force
```

Review the generated files to ensure they match your expectations.

### 4. Team Adoption

Share the configuration with your team:

1. Commit `config/turbomaker.php` to version control
2. Document any custom paths or conventions
3. Provide team training on the configuration

## Configuration Validation

Create a validation command to check configuration:

**app/Console/Commands/ValidateTurbomakerConfig.php**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateTurbomakerConfig extends Command
{
    protected $signature = 'turbomaker:validate-config';
    protected $description = 'Validate TurboMaker configuration';

    public function handle()
    {
        $config = config('turbomaker');

        $this->info('Validating TurboMaker configuration...');

        // Check required paths exist
        $paths = $config['paths'] ?? [];
        foreach ($paths as $type => $path) {
            $fullPath = base_path($path);
            if (!is_dir($fullPath)) {
                $this->warn("Path does not exist: {$path}");
                if ($this->confirm("Create directory {$path}?")) {
                    mkdir($fullPath, 0755, true);
                    $this->info("Created directory: {$path}");
                }
            } else {
                $this->info("✓ Path exists: {$path}");
            }
        }

        // Check stub templates
        $stubPath = $config['stubs']['path'] ?? resource_path('stubs/turbomaker');
        if (!is_dir($stubPath)) {
            $this->error("Stub path does not exist: {$stubPath}");
            $this->line("Run: php artisan vendor:publish --tag=turbomaker-stubs");
        } else {
            $this->info("✓ Stub path exists: {$stubPath}");
        }

        // Validate custom templates
        $customTemplates = $config['stubs']['custom'] ?? [];
        foreach ($customTemplates as $type => $template) {
            if (!file_exists($template)) {
                $this->error("Custom template not found: {$template}");
            } else {
                $this->info("✓ Custom template exists: {$template}");
            }
        }

        $this->info('Configuration validation completed!');
    }
}
```

Register the command in `app/Console/Kernel.php`:

```php
protected $commands = [
    Commands\ValidateTurbomakerConfig::class,
];
```

## Migration Between Configurations

When switching configurations, use this migration script:

**migrate-turbomaker-config.sh**

```bash
#!/bin/bash

echo "Migrating TurboMaker configuration..."

# Backup current configuration
if [ -f config/turbomaker.php ]; then
    cp config/turbomaker.php config/turbomaker.php.backup
    echo "Backed up current configuration to config/turbomaker.php.backup"
fi

# Copy new configuration
if [ "$1" ]; then
    cp "examples/configuration/$1.php" config/turbomaker.php
    echo "Applied $1 configuration"
else
    echo "Usage: ./migrate-turbomaker-config.sh [team|enterprise|prototype|api|microservices]"
    exit 1
fi

# Validate new configuration
php artisan turbomaker:validate-config

echo "Configuration migration completed!"
```

Use it like this:

```bash
./migrate-turbomaker-config.sh enterprise
./migrate-turbomaker-config.sh api
./migrate-turbomaker-config.sh team
```

## Best Practices

1. **Start Simple**: Begin with basic configuration and gradually add complexity
2. **Version Control**: Always commit configuration files
3. **Document Decisions**: Comment why specific choices were made
4. **Test Regularly**: Validate configuration with test generations
5. **Team Alignment**: Ensure all team members use the same configuration
6. **Environment Specific**: Use environment variables for settings that vary
7. **Regular Review**: Periodically review and update configuration as project evolves

These configuration examples provide solid foundations for different project types and team structures!