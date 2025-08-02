# Custom Field Types for Laravel TurboMaker

This directory contains examples of how to create custom field types for Laravel TurboMaker.

## Creating a Custom Field Type

To create a custom field type, follow these steps:

### 1. Create the Field Type Class

Create a class that implements `FieldTypeInterface` or extends `AbstractFieldType`:

```php
<?php

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

final class MyCustomFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string'; // Or whatever column type you need
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['string']; // Base validation rules
        
        // Add your custom validation logic here
        
        return array_merge($rules, $this->getCommonValidationRules($field));
    }

    public function getFactoryDefinition(Field $field): string
    {
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }
        
        return 'fake()->word()'; // Your factory logic
    }

    public function getCastType(Field $field): ?string
    {
        return null; // Return cast type if needed
    }
}
```

### 2. Register the Field Type

Add your custom field type to the `config/turbomaker.php` configuration:

```php
'custom_field_types' => [
    'my_custom_type' => App\TurboMaker\FieldTypes\MyCustomFieldType::class,
],
```

### 3. Use in Schema Files

Now you can use your custom field type in schema files:

```yaml
fields:
  my_field:
    type: my_custom_type
    nullable: false
    attributes:
      custom_option: value
```

## Examples

### MoneyFieldType
- Handles monetary values with decimal precision
- Validates positive numbers with scale constraints
- Generates realistic money amounts in factories
- Proper decimal casting

### SlugFieldType
- Handles URL-friendly slugs
- Validates slug format (lowercase, hyphens only)
- Generates proper slugs in factories
- No special casting needed

## Available Methods

### Required Methods (from FieldTypeInterface)

- `getMigrationDefinition(Field $field): string` - Return the Laravel column type
- `getValidationRules(Field $field): array` - Return validation rules array
- `getFactoryDefinition(Field $field): string` - Return Faker method chain
- `getCastType(Field $field): ?string` - Return Eloquent cast type or null

### Optional Methods (from AbstractFieldType)

- `getMigrationModifiers(Field $field): array` - Return column modifiers (nullable, unique, etc.)
- `getCommonValidationRules(Field $field): array` - Get standard validation rules
- `getStringFactoryByName(Field $field): string` - Name-based factory generation for strings

## Field Properties Available

- `$field->name` - Field name
- `$field->type` - Field type
- `$field->nullable` - Whether field is nullable
- `$field->unique` - Whether field is unique
- `$field->index` - Whether field is indexed
- `$field->default` - Default value
- `$field->length` - Field length (for strings)
- `$field->comment` - Field comment
- `$field->attributes` - Custom attributes array
- `$field->validationRules` - Custom validation rules
- `$field->factoryRules` - Custom factory rules

## Tips

1. Extend `AbstractFieldType` instead of implementing `FieldTypeInterface` directly
2. Use `$field->attributes` for custom field-specific options
3. Always merge with `getCommonValidationRules()` for consistent behavior
4. Handle custom factory rules by checking `$field->factoryRules` first
5. Use `array_unique()` on validation rules to avoid duplicates
