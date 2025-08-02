<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class DateTimeFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'dateTime';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['date'];

        // Add date-specific validations
        if (isset($field->attributes['after'])) {
            $rules[] = "after:{$field->attributes['after']}";
        }
        if (isset($field->attributes['before'])) {
            $rules[] = "before:{$field->attributes['before']}";
        }

        // Merge with custom rules
        $rules = array_merge($rules, $this->getCommonValidationRules($field));

        return array_unique($rules);
    }

    public function getFactoryDefinition(Field $field): string
    {
        // Check for custom factory rules first
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        // Smart datetime generation based on field name
        $name = mb_strtolower($field->name);
        
        return match (true) {
            str_contains($name, 'publish') => "fake()->optional(0.7)->dateTimeBetween('-1 year', 'now')",
            str_contains($name, 'login') || str_contains($name, 'last_') => "fake()->dateTimeBetween('-30 days', 'now')",
            str_contains($name, 'expire') => "fake()->dateTimeBetween('+1 month', '+5 years')",
            default => "fake()->dateTime()",
        };
    }

    public function getCastType(Field $field): ?string
    {
        return 'datetime';
    }
}
