<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

abstract class AbstractFieldType implements FieldTypeInterface
{
    /**
     * Get the migration column modifiers
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

        if ($field->default !== null) {
            $defaultValue = is_string($field->default) ? "'{$field->default}'" : $field->default;
            $modifiers[] = "default({$defaultValue})";
        }

        if ($field->comment !== null && $field->comment !== '' && $field->comment !== '0') {
            $modifiers[] = "comment('{$field->comment}')";
        }

        return $modifiers;
    }

    /**
     * Get common validation rules that apply to most field types
     */
    protected function getCommonValidationRules(Field $field): array
    {
        $rules = [];

        // Custom validation rules from schema
        if ($field->validationRules !== []) {
            return array_merge($rules, $field->validationRules);
        }

        return $rules;
    }

    /**
     * Get factory definition based on field name patterns
     */
    protected function getStringFactoryByName(Field $field): string
    {
        $name = mb_strtolower($field->name);

        return match (true) {
            str_contains($name, 'email') => 'fake()->unique()->safeEmail()',
            str_contains($name, 'name') && str_contains($name, 'first') => 'fake()->firstName()',
            str_contains($name, 'name') && str_contains($name, 'last') => 'fake()->lastName()',
            str_contains($name, 'name') => 'fake()->name()',
            str_contains($name, 'phone') => 'fake()->phoneNumber()',
            str_contains($name, 'address') => 'fake()->address()',
            str_contains($name, 'city') => 'fake()->city()',
            str_contains($name, 'country') => 'fake()->country()',
            str_contains($name, 'url') => 'fake()->url()',
            str_contains($name, 'slug') => 'fake()->slug()',
            str_contains($name, 'title') => 'fake()->sentence(4)',
            str_contains($name, 'description') => 'fake()->paragraph()',
            default => 'fake()->words(3, true)',
        };
    }
}
