<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class SmallIntegerFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'smallInteger';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['integer', 'min:-32768', 'max:32767'];

        // If unsigned, adjust range
        if (isset($field->attributes['unsigned']) && $field->attributes['unsigned']) {
            $rules = ['integer', 'min:0', 'max:65535'];
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

        $min = isset($field->attributes['unsigned']) && $field->attributes['unsigned'] ? 0 : -32768;
        $max = isset($field->attributes['unsigned']) && $field->attributes['unsigned'] ? 65535 : 32767;

        return "fake()->numberBetween({$min}, {$max})";
    }

    public function getCastType(Field $field): ?string
    {
        return 'integer';
    }
}
