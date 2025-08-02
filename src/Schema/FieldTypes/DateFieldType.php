<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class DateFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'date';
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
        if (isset($field->attributes['after_or_equal'])) {
            $rules[] = "after_or_equal:{$field->attributes['after_or_equal']}";
        }
        if (isset($field->attributes['before_or_equal'])) {
            $rules[] = "before_or_equal:{$field->attributes['before_or_equal']}";
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

        // Smart date generation based on field name
        $name = mb_strtolower($field->name);

        return match (true) {
            str_contains($name, 'birth') => "fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d')",
            str_contains($name, 'start') => "fake()->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d')",
            str_contains($name, 'end') => "fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d')",
            str_contains($name, 'expire') => "fake()->dateTimeBetween('+1 month', '+5 years')->format('Y-m-d')",
            default => 'fake()->date()',
        };
    }

    public function getCastType(Field $field): ?string
    {
        return 'date';
    }
}
