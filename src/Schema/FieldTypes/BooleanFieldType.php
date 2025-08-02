<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class BooleanFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'boolean';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['boolean'];

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

        return 'fake()->boolean()';
    }

    public function getCastType(Field $field): ?string
    {
        return 'boolean';
    }

    /**
     * Override to handle boolean default values properly
     */
    public function getMigrationModifiers(Field $field): array
    {
        $modifiers = [];

        if ($field->nullable) {
            $modifiers[] = 'nullable()';
        }

        if ($field->unique) {
            $modifiers[] = 'unique()';
        }

        if ($field->index) {
            $modifiers[] = 'index()';
        }

        // Special handling for boolean defaults
        if ($field->default !== null) {
            $defaultValue = $field->default === true || $field->default === 'true' || $field->default === 1 || $field->default === '1' 
                ? 'true' 
                : 'false';
            $modifiers[] = "default({$defaultValue})";
        }

        if ($field->comment !== null && $field->comment !== '' && $field->comment !== '0') {
            $modifiers[] = "comment('{$field->comment}')";
        }

        return $modifiers;
    }
}
