# Custom Field Types

Laravel TurboMaker's extensible architecture allows you to create custom field types for your specific needs. This guide shows you how to create, register, and use custom field types.

## Why Custom Field Types?

Custom field types are useful when you need:
- **Domain-specific validation** (e.g., ISBN numbers, SKUs)
- **Special formatting** (e.g., phone numbers, social security numbers)
- **Complex data structures** (e.g., coordinates, money with currency)
- **Business rules** (e.g., custom date ranges, approval workflows)

## Creating a Custom Field Type

### 1. Create the Field Type Class

Create a class that extends `AbstractFieldType`:

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

        // Add scale validation if specified
        if (isset($field->attributes['scale'])) {
            $scale = $field->attributes['scale'];
            $rules[] = "regex:/^\d+(\.\d{1,{$scale}})?$/";
        }

        // Max amount validation
        if (isset($field->attributes['max_amount'])) {
            $rules[] = "max:{$field->attributes['max_amount']}";
        }

        // Merge with common rules (nullable, unique, etc.)
        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        // Check for custom factory rules first
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        // Generate realistic money amounts
        $minAmount = $field->attributes['min_amount'] ?? 10;
        $maxAmount = $field->attributes['max_amount'] ?? 9999;
        $scale = $field->attributes['scale'] ?? 2;

        return "fake()->randomFloat({$scale}, {$minAmount}, {$maxAmount})";
    }

    public function getCastType(Field $field): ?string
    {
        $scale = $field->attributes['scale'] ?? 2;
        return "decimal:{$scale}";
    }
}
```

### 2. Register the Field Type

Add your custom field type to `config/turbomaker.php`:

```php
<?php

return [
    // ... other configuration

    'custom_field_types' => [
        'money' => App\TurboMaker\FieldTypes\MoneyFieldType::class,
        'slug' => App\TurboMaker\FieldTypes\SlugFieldType::class,
        'phone' => App\TurboMaker\FieldTypes\PhoneFieldType::class,
    ],
];
```

### 3. Use in Schema Files

Now you can use your custom field type in schemas:

```yaml
# resources/schemas/product.schema.yml
fields:
  price:
    type: money
    attributes:
      precision: 10
      scale: 2
      min_amount: 0
      max_amount: 99999
    validation: ["required"]
    
  wholesale_price:
    type: money
    nullable: true
    attributes:
      scale: 3
```

## More Examples

### Slug Field Type

```php
<?php

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

final class SlugFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = [
            'string',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // Lowercase letters, numbers, hyphens
        ];

        // Length validation
        if ($field->length) {
            $rules[] = "max:{$field->length}";
        } else {
            $rules[] = 'max:255';
        }

        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        return 'fake()->slug()';
    }

    public function getCastType(Field $field): ?string
    {
        return null; // Slugs are strings, no casting needed
    }
}
```

### Phone Number Field Type

```php
<?php

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

final class PhoneFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['string'];

        // Country-specific validation
        $country = $field->attributes['country'] ?? 'international';
        
        switch ($country) {
            case 'us':
                $rules[] = 'regex:/^\+?1?[0-9]{10}$/';
                break;
            case 'fr':
                $rules[] = 'regex:/^(\+33|0)[1-9](\d{8})$/';
                break;
            default:
                $rules[] = 'regex:/^\+?[1-9]\d{1,14}$/'; // E.164 format
        }

        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        $country = $field->attributes['country'] ?? 'international';
        
        return match ($country) {
            'us' => 'fake()->phoneNumber()',
            'fr' => 'fake()->regexify("0[1-9][0-9]{8}")',
            default => 'fake()->e164PhoneNumber()',
        };
    }

    public function getCastType(Field $field): ?string
    {
        return null;
    }
}
```

### Coordinates Field Type

```php
<?php

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

final class CoordinatesFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'json'; // Store as JSON: {"lat": 48.8566, "lng": 2.3522}
    }

    public function getValidationRules(Field $field): array
    {
        $rules = [
            'array',
            'required_array_keys:lat,lng',
        ];

        // Latitude validation (-90 to 90)
        $rules[] = 'lat';
        $rules[] = 'numeric';
        $rules[] = 'between:-90,90';

        // Longitude validation (-180 to 180)
        $rules[] = 'lng';
        $rules[] = 'numeric';
        $rules[] = 'between:-180,180';

        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        return '[
            "lat" => fake()->latitude(),
            "lng" => fake()->longitude()
        ]';
    }

    public function getCastType(Field $field): ?string
    {
        return 'array';
    }
}
```

### Color Field Type

```php
<?php

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

final class ColorFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['string'];

        // Color format validation
        $format = $field->attributes['format'] ?? 'hex';
        
        switch ($format) {
            case 'hex':
                $rules[] = 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
                break;
            case 'rgb':
                $rules[] = 'regex:/^rgb\((\d{1,3}),\s?(\d{1,3}),\s?(\d{1,3})\)$/';
                break;
            case 'hsl':
                $rules[] = 'regex:/^hsl\((\d{1,3}),\s?(\d{1,3})%,\s?(\d{1,3})%\)$/';
                break;
        }

        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        $format = $field->attributes['format'] ?? 'hex';
        
        return match ($format) {
            'hex' => 'fake()->hexColor()',
            'rgb' => 'fake()->rgbColor()',
            default => 'fake()->hexColor()',
        };
    }

    public function getCastType(Field $field): ?string
    {
        return null;
    }
}
```

## Understanding the Interface

All field types must implement these methods:

### Required Methods

```php
interface FieldTypeInterface
{
    /**
     * Get the Laravel migration column type
     */
    public function getMigrationDefinition(Field $field): string;

    /**
     * Get validation rules for forms
     */
    public function getValidationRules(Field $field): array;

    /**
     * Get factory definition for testing
     */
    public function getFactoryDefinition(Field $field): string;

    /**
     * Get Eloquent cast type (or null)
     */
    public function getCastType(Field $field): ?string;

    /**
     * Get migration column modifiers (nullable, unique, etc.)
     */
    public function getMigrationModifiers(Field $field): array;
}
```

### Helper Methods from AbstractFieldType

When extending `AbstractFieldType`, you get these helper methods:

```php
abstract class AbstractFieldType implements FieldTypeInterface
{
    /**
     * Get common validation rules (nullable, unique, etc.)
     */
    protected function getCommonValidationRules(Field $field): array;

    /**
     * Get standard migration modifiers
     */
    public function getMigrationModifiers(Field $field): array;

    /**
     * Generate string factory based on field name
     */
    protected function getStringFactoryByName(Field $field): string;
}
```

## Field Properties Available

Your custom field type has access to all field properties:

```php
public function getValidationRules(Field $field): array
{
    // Basic properties
    $field->name;           // Field name
    $field->type;           // Field type (your custom type)
    $field->nullable;       // boolean
    $field->unique;         // boolean
    $field->index;          // boolean
    $field->default;        // mixed
    $field->length;         // int|null
    $field->comment;        // string|null
    
    // Custom data
    $field->attributes;     // array - custom attributes
    $field->validationRules; // array - custom validation rules
    $field->factoryRules;   // array - custom factory rules
}
```

## Usage Examples

### Money Field in Schema

```yaml
fields:
  price:
    type: money
    attributes:
      precision: 10
      scale: 2
      min_amount: 0
      max_amount: 999999
    validation: ["required"]
    
  discount_amount:
    type: money
    nullable: true
    attributes:
      scale: 3
```

### Phone Field in Schema

```yaml
fields:
  phone:
    type: phone
    nullable: true
    attributes:
      country: "fr"
      
  us_phone:
    type: phone
    attributes:
      country: "us"
```

### Generated Code Examples

#### Model
```php
class Product extends Model
{
    protected $fillable = ['price', 'discount_amount'];
    
    protected $casts = [
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:3',
    ];
}
```

#### Migration
```php
Schema::create('products', function (Blueprint $table) {
    $table->decimal('price', 10, 2);
    $table->decimal('discount_amount', 10, 3)->nullable();
});
```

#### Form Request
```php
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
```

#### Factory
```php
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'price' => fake()->randomFloat(2, 10, 9999),
            'discount_amount' => fake()->randomFloat(3, 10, 9999),
        ];
    }
}
```

## Best Practices

### 1. Extend AbstractFieldType
Always extend `AbstractFieldType` instead of implementing `FieldTypeInterface` directly.

### 2. Handle Custom Factory Rules
Always check for custom factory rules first:

```php
public function getFactoryDefinition(Field $field): string
{
    if ($field->factoryRules !== []) {
        return implode('->', $field->factoryRules);
    }
    
    // Your custom logic here
}
```

### 3. Use Field Attributes
Use the `attributes` array for type-specific configuration:

```yaml
field_name:
  type: custom_type
  attributes:
    option1: value1
    option2: value2
```

### 4. Merge Common Rules
Always merge with common validation rules:

```php
return array_merge($rules, $this->getCommonValidationRules($field));
```

### 5. Avoid Duplicates
Use `array_unique()` when combining rules to avoid duplicates.

## Advanced Examples

### Enum Field Type

```php
final class EnumFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['string'];
        
        // Add enum values from attributes
        if (isset($field->attributes['values'])) {
            $values = implode(',', $field->attributes['values']);
            $rules[] = "in:{$values}";
        }

        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        $values = $field->attributes['values'] ?? ['active', 'inactive'];
        $valuesString = implode('", "', $values);
        
        return "fake()->randomElement([\"{$valuesString}\"])";
    }

    public function getCastType(Field $field): ?string
    {
        return null;
    }
}
```

Usage:
```yaml
status:
  type: enum
  attributes:
    values: ["draft", "published", "archived"]
  default: "draft"
```

## Testing Custom Field Types

Create tests for your custom field types:

```php
<?php

use App\TurboMaker\FieldTypes\MoneyFieldType;
use Grazulex\LaravelTurbomaker\Schema\Field;

it('generates correct validation rules for money field', function () {
    $fieldType = new MoneyFieldType();
    $field = new Field(
        name: 'price',
        type: 'money',
        attributes: ['scale' => 2, 'max_amount' => 9999]
    );

    $rules = $fieldType->getValidationRules($field);

    expect($rules)->toContain('numeric');
    expect($rules)->toContain('min:0');
    expect($rules)->toContain('max:9999');
});
```

## Next Steps

- **[[Configuration]]** - Learn about configuration options
- **[[Schema System]]** - Complete schema documentation
- **[[Examples]]** - See real-world examples
- **[[Testing]]** - Testing strategies for custom field types

---

**Ready to create powerful, reusable field types? Start building your first custom field type today!**
