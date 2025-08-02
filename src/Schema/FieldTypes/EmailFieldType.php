<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class EmailFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['email'];

        // Length validation (emails are typically limited)
        $rules[] = $field->length !== null && $field->length !== 0 ? "max:{$field->length}" : 'max:255';

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

        return 'fake()->unique()->safeEmail()';
    }

    public function getCastType(Field $field): ?string
    {
        return null;
    }
}
