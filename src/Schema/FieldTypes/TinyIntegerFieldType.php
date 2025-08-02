<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class TinyIntegerFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'tinyInteger';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['integer', 'min:-128', 'max:127'];

        // If unsigned, adjust range
        if (isset($field->attributes['unsigned']) && $field->attributes['unsigned']) {
            $rules = ['integer', 'min:0', 'max:255'];
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

        $min = isset($field->attributes['unsigned']) && $field->attributes['unsigned'] ? 0 : -128;
        $max = isset($field->attributes['unsigned']) && $field->attributes['unsigned'] ? 255 : 127;

        return "fake()->numberBetween({$min}, {$max})";
    }

    public function getCastType(Field $field): ?string
    {
        return 'integer';
    }
}
