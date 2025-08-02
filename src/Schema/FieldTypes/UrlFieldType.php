<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class UrlFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'string';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['url'];

        // Length validation
        if ($field->length) {
            $rules[] = "max:{$field->length}";
        } else {
            $rules[] = 'max:2048'; // URLs can be long
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

        return 'fake()->url()';
    }

    public function getCastType(Field $field): ?string
    {
        return null;
    }
}
