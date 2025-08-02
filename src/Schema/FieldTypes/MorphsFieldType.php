<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class MorphsFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'morphs';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = [];

        // For morphs, we typically validate the individual _id and _type fields
        // This is handled at the relationship level, not field level

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

        // Morphs creates two fields, so this is handled differently
        return '// Morphs will be handled by relationship factory';
    }

    public function getCastType(Field $field): ?string
    {
        return null; // Morphs don't need casting
    }
}
