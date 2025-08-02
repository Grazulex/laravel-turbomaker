<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class MediumIntegerFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'mediumInteger';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['integer', 'min:-8388608', 'max:8388607'];

        // If unsigned, adjust range
        if (isset($field->attributes['unsigned']) && $field->attributes['unsigned']) {
            $rules = ['integer', 'min:0', 'max:16777215'];
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

        $min = isset($field->attributes['unsigned']) && $field->attributes['unsigned'] ? 0 : -8388608;
        $max = isset($field->attributes['unsigned']) && $field->attributes['unsigned'] ? 16777215 : 8388607;

        return "fake()->numberBetween({$min}, {$max})";
    }

    public function getCastType(Field $field): ?string
    {
        return 'integer';
    }
}
