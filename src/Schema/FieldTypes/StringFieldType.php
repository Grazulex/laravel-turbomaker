<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class StringFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['string'];

        // Length validation
        if ($field->length !== null && $field->length !== 0) {
            $rules[] = "max:{$field->length}";
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

        // Use name-based detection
        return $this->getStringFactoryByName($field);
    }

    public function getCastType(Field $field): ?string
    {
        return null; // Strings don't need casting
    }
}
