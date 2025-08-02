# Advanced Configuration

Complete configuration guide for Laravel TurboMaker.

## Publishing Configuration

```bash
# Publish main configuration
php artisan vendor:publish --tag=turbomaker-config

# Publish stub templates
php artisan vendor:publish --tag=turbomaker-stubs
```

## Configuration File Structure

The configuration file `config/turbomaker.php` contains all customizable settings:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package Settings
    |--------------------------------------------------------------------------
    */
    'enabled' => env('TURBOMAKER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Generation Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
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
    | Custom Field Types (V6)
    |--------------------------------------------------------------------------
    */
    'custom_field_types' => [
        // 'money' => App\TurboMaker\FieldTypes\MoneyFieldType::class,
        // 'slug' => App\TurboMaker\FieldTypes\SlugFieldType::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_enabled' => env('TURBOMAKER_CACHE_ENABLED', true),
        'cache_ttl' => env('TURBOMAKER_CACHE_TTL', 3600),
        'memory_limit' => env('TURBOMAKER_MEMORY_LIMIT', '512M'),
    ],
];
```

## Environment Variables

You can control TurboMaker behavior via environment variables:

```env
# .env file

# Enable/disable the package
TURBOMAKER_ENABLED=true

# Default generation settings
TURBOMAKER_GENERATE_TESTS=true
TURBOMAKER_GENERATE_FACTORY=true
TURBOMAKER_GENERATE_SEEDER=false
TURBOMAKER_GENERATE_POLICIES=false
TURBOMAKER_GENERATE_API_RESOURCES=true
TURBOMAKER_GENERATE_ACTIONS=false
TURBOMAKER_GENERATE_SERVICES=false
TURBOMAKER_GENERATE_RULES=false
TURBOMAKER_GENERATE_OBSERVERS=false

# Performance settings
TURBOMAKER_CACHE_ENABLED=true
TURBOMAKER_CACHE_TTL=3600
TURBOMAKER_MEMORY_LIMIT=512M
```

## Custom Field Types

Laravel TurboMaker V6 introduces extensible field types. You can create and register custom field types:

### 1. Create a Custom Field Type

```php
<?php

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

final class MoneyFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'decimal';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['numeric', 'min:0'];
        
        // Add precision validation
        if (isset($field->attributes['max_amount'])) {
            $rules[] = "max:{$field->attributes['max_amount']}";
        }
        
        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }
        
        $min = $field->attributes['min_amount'] ?? 10;
        $max = $field->attributes['max_amount'] ?? 9999;
        $scale = $field->attributes['scale'] ?? 2;
        
        return "fake()->randomFloat({$scale}, {$min}, {$max})";
    }

    public function getCastType(Field $field): ?string
    {
        $scale = $field->attributes['scale'] ?? 2;
        return "decimal:{$scale}";
    }
}
```

### 2. Register the Field Type

```php
// config/turbomaker.php
'custom_field_types' => [
    'money' => App\TurboMaker\FieldTypes\MoneyFieldType::class,
    'slug' => App\TurboMaker\FieldTypes\SlugFieldType::class,
    'coordinates' => App\TurboMaker\FieldTypes\CoordinatesFieldType::class,
],
```

### 3. Use in Schema Files

```yaml
# resources/schemas/product.schema.yml
fields:
  price:
    type: money
    nullable: false
    attributes:
      precision: 10
      scale: 2
      max_amount: 99999.99
    validation: ["min:0.01"]
```

## Custom Stub Templates

TurboMaker uses stub templates to generate code. You can customize these templates:

### 1. Publish Stubs

```bash
php artisan vendor:publish --tag=turbomaker-stubs
```

This creates templates in `resources/stubs/turbomaker/`:

```
resources/stubs/turbomaker/
├── action.create.stub
├── action.delete.stub
├── action.get.stub
├── action.update.stub
├── controller.api.stub
├── controller.stub
├── factory.stub
├── migration.stub
├── model.stub
├── observer.stub
├── policy.stub
├── request.store.stub
├── request.update.stub
├── resource.stub
├── rule.exists.stub
├── rule.unique.stub
├── seeder.stub
├── service.stub
├── test.feature.stub
├── test.unit.stub
├── view.create.stub
├── view.edit.stub
├── view.index.stub
└── view.show.stub
```

### 2. Customize Templates

Templates use placeholders that get replaced during generation:

```php
// model.stub example
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
{{ use_statements }}

final class {{ class_name }} extends Model
{
    use HasFactory;

    protected $fillable = [
{{ fillable }}
    ];

    protected $casts = [
{{ casts }}
    ];

{{ relationships }}
}
```

### 3. Available Placeholders

| Placeholder | Description | Example |
|-------------|-------------|---------|
| `{{ namespace }}` | Class namespace | `App\Models` |
| `{{ class_name }}` | Class name | `Product` |
| `{{ table_name }}` | Database table | `products` |
| `{{ fillable }}` | Fillable attributes | `'name', 'price'` |
| `{{ casts }}` | Model casts | `'active' => 'boolean'` |
| `{{ relationships }}` | Model relationships | `public function category()...` |
| `{{ use_statements }}` | Import statements | `use App\Models\Category;` |
| `{{ validation_rules }}` | Validation rules | `'name' => 'required|string'` |
| `{{ factory_definition }}` | Factory attributes | `'name' => fake()->name()` |

## Schema Configuration

### Schema File Locations

TurboMaker looks for schema files in these locations:

1. `resources/schemas/` (default)
2. `storage/schemas/`
3. Custom path via config

### Schema File Structure

```yaml
# Complete schema file example
name: "Product"
table_name: "products"

fields:
  id:
    type: unsignedBigInteger
    nullable: false
    unique: true
    comment: "Primary key"
    
  name:
    type: string
    length: 255
    nullable: false
    index: true
    validation: ["min:3", "max:255"]
    
  slug:
    type: string
    length: 255
    nullable: false
    unique: true
    validation: ["regex:/^[a-z0-9-]+$/"]
    factory: ["slug()"]
    
  price:
    type: money  # Custom field type
    nullable: false
    attributes:
      precision: 10
      scale: 2
      max_amount: 99999.99
    validation: ["min:0.01"]
    
  is_active:
    type: boolean
    nullable: false
    default: true

relationships:
  category:
    type: belongsTo
    model: "Category"
    foreign_key: "category_id"
    
  tags:
    type: belongsToMany
    model: "Tag"
    pivot_table: "product_tags"
    pivot_fields: ["priority", "created_at"]
```

## Performance Optimization

### Caching

```php
// config/turbomaker.php
'performance' => [
    'cache_enabled' => true,
    'cache_ttl' => 3600, // 1 hour
    'memory_limit' => '512M',
],
```

### Memory Management

For large projects, increase memory limits:

```env
TURBOMAKER_MEMORY_LIMIT=1024M
```

### Batch Generation

Generate multiple models efficiently:

```php
// Custom Artisan command for batch generation
$models = ['User', 'Product', 'Order', 'Category'];

foreach ($models as $model) {
    Artisan::call('turbo:make', [
        'name' => $model,
        '--schema' => strtolower($model),
        '--tests' => true,
        '--factory' => true,
    ]);
}
```

## Testing Configuration

### Test Framework

TurboMaker generates tests using Pest by default. Configure test generation:

```php
// config/turbomaker.php
'defaults' => [
    'generate_tests' => true,
],

'paths' => [
    'tests' => 'tests',
    'feature_tests' => 'tests/Feature',
    'unit_tests' => 'tests/Unit',
],
```

### Custom Test Templates

Customize test templates in `resources/stubs/turbomaker/`:

```php
// test.feature.stub
<?php

declare(strict_types=1);

use App\Models\{{ class_name }};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create {{ class_name_lower }}', function () {
    ${{ class_name_lower }} = {{ class_name }}::factory()->create();
    
    expect(${{ class_name_lower }})->toBeInstanceOf({{ class_name }}::class);
});

it('can update {{ class_name_lower }}', function () {
    ${{ class_name_lower }} = {{ class_name }}::factory()->create();
    
    ${{ class_name_lower }}->update(['name' => 'Updated Name']);
    
    expect(${{ class_name_lower }}->name)->toBe('Updated Name');
});
```

## Development Environment

### Local Development

```env
# .env.local
TURBOMAKER_ENABLED=true
TURBOMAKER_GENERATE_TESTS=true
TURBOMAKER_CACHE_ENABLED=false  # Disable cache during development
```

### Production

```env
# .env.production  
TURBOMAKER_ENABLED=false  # Disable in production
```

### CI/CD Integration

```yaml
# GitHub Actions example
- name: Generate Models
  run: |
    php artisan turbo:make User --schema=user --tests --factory
    php artisan turbo:make Product --schema=product --tests --factory
    composer test
```

## Troubleshooting

### Common Configuration Issues

**Config not loading:**
```bash
php artisan config:clear
php artisan config:cache
```

**Custom field types not recognized:**
```bash
# Check configuration
php artisan config:show turbomaker.custom_field_types

# Clear cache
php artisan turbo:schema clear-cache
```

**Stub templates not found:**
```bash
# Republish stubs
php artisan vendor:publish --tag=turbomaker-stubs --force
```

### Debug Mode

Enable detailed output:

```php
// config/turbomaker.php
'debug' => env('TURBOMAKER_DEBUG', false),
```

```env
TURBOMAKER_DEBUG=true
```

This provides detailed information about generation process and any issues encountered.
