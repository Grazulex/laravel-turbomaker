<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class BinaryFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'binary';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = [];

        // Binary fields are typically handled differently
        // Could add custom validation if needed

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

        return 'fake()->randomElements([0, 1], 32, true)';
    }

    public function getCastType(Field $field): ?string
    {
        return null;
    }
}
