<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class ForeignIdFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'foreignId';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['integer'];

        // Add exists validation if constrained table is specified
        if (isset($field->attributes['constrained'])) {
            if (is_string($field->attributes['constrained'])) {
                $table = $field->attributes['constrained'];
            } else {
                // Try to guess table name from field name
                $table = str_replace('_id', 's', $field->name);
            }
            $rules[] = "exists:{$table},id";
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

        return 'fake()->numberBetween(1, 999999)';
    }

    public function getCastType(Field $field): ?string
    {
        return 'integer';
    }

    /**
     * Override to handle foreign key constraints
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

        // Handle constrained for foreign keys
        if (isset($field->attributes['constrained'])) {
            if (is_string($field->attributes['constrained'])) {
                $modifiers[] = "constrained('{$field->attributes['constrained']}')";
            } else {
                $modifiers[] = "constrained()";
            }
        }

        if ($field->default !== null) {
            $defaultValue = is_string($field->default) ? "'{$field->default}'" : $field->default;
            $modifiers[] = "default({$defaultValue})";
        }

        if ($field->comment !== null && $field->comment !== '' && $field->comment !== '0') {
            $modifiers[] = "comment('{$field->comment}')";
        }

        return $modifiers;
    }
}
