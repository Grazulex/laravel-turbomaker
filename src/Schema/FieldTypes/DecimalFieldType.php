<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class DecimalFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        $precision = $field->attributes['precision'] ?? 8;
        $scale = $field->attributes['scale'] ?? 2;
        return "decimal({$precision}, {$scale})";
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['numeric'];

        // Min/max validation
        if (isset($field->attributes['min'])) {
            $rules[] = "min:{$field->attributes['min']}";
        }
        if (isset($field->attributes['max'])) {
            $rules[] = "max:{$field->attributes['max']}";
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

        $scale = $field->attributes['scale'] ?? 2;
        $min = $field->attributes['min'] ?? 0;
        $max = $field->attributes['max'] ?? 999.99;
        
        return "fake()->randomFloat({$scale}, {$min}, {$max})";
    }

    public function getCastType(Field $field): ?string
    {
        $scale = $field->attributes['scale'] ?? 2;
        return "decimal:{$scale}";
    }
}
