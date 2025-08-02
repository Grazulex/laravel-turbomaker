<?php

declare(strict_types=1);

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

/**
 * Custom Slug field type - Example of how to extend the system
 * 
 * This field type handles URL-friendly slugs with proper validation,
 * factory generation, and specific rules.
 */
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
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // Only lowercase letters, numbers, and hyphens
        ];

        // Length validation
        if ($field->length) {
            $rules[] = "max:{$field->length}";
        } else {
            $rules[] = 'max:255'; // Default max length for slugs
        }

        // Merge with common validation rules
        $rules = array_merge($rules, $this->getCommonValidationRules($field));

        return array_unique($rules);
    }

    public function getFactoryDefinition(Field $field): string
    {
        // Check for custom factory rules first
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        // Generate URL-friendly slugs
        return 'fake()->slug()';
    }

    public function getCastType(Field $field): ?string
    {
        return null; // Slugs don't need casting, they're strings
    }
}
