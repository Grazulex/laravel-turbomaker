<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class UuidFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'uuid';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['uuid'];

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

        return 'fake()->uuid()';
    }

    public function getCastType(Field $field): ?string
    {
        return 'string';
    }
}
