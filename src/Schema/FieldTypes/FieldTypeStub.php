<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

/**
 * Legacy FieldType Stub
 * Provides basic field type functionality for backward compatibility
 * Real field type handling is now done by ModelSchema Enterprise
 */
final class FieldTypeStub
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Get migration definition for a field (just the base type)
     */
    public function getMigrationDefinition(): string
    {
        // Map certain logical types to their migration equivalents
        return match ($this->type) {
            'email' => 'string', // emails are stored as strings in migrations
            'url' => 'string',   // urls are stored as strings in migrations
            default => $this->type,
        };
    }

    /**
     * Get full migration column definition
     */
    public function getFullMigrationDefinition(Field $field): string
    {
        $definition = "\$table->{$this->type}('{$field->name}')";

        if ($field->length !== null && $field->length !== 0) {
            $definition = "\$table->{$this->type}('{$field->name}', {$field->length})";
        }

        if ($field->nullable) {
            $definition .= '->nullable()';
        }

        if ($field->default !== null) {
            $defaultValue = is_string($field->default) ? "'{$field->default}'" : $field->default;
            $definition .= "->default({$defaultValue})";
        }

        if ($field->unique) {
            $definition .= '->unique()';
        }

        if ($field->index) {
            $definition .= '->index()';
        }

        return $definition;
    }

    /**
     * Get PHP cast type for the field
     */
    public function getCastType(): ?string
    {
        return match ($this->type) {
            'integer' => 'int',
            'boolean' => 'bool',
            'date' => 'date',
            'array' => 'array',
            default => null,
        };
    }

    /**
     * Get validation rules for the field
     */
    public function getValidationRules(): array
    {
        return [
            // Basic type validation
            match ($this->type) {
                'integer' => 'integer',
                'boolean' => 'boolean',
                'email' => 'email',
                'url' => 'url',
                'date' => 'date',
                'string' => 'string',
                'array' => 'array',
                default => 'required',
            },
        ];
    }

    /**
     * Get migration modifiers for this field type
     */
    public function getMigrationModifiers(Field $field): array
    {
        $modifiers = [];

        // Add nullable if specified
        if ($field->nullable) {
            $modifiers[] = 'nullable()';
        }

        // Add default if specified
        if ($field->default !== null) {
            $defaultValue = is_string($field->default) ? "'{$field->default}'" : $field->default;
            $modifiers[] = "default({$defaultValue})";
        }

        // Add unique if specified
        if ($field->unique) {
            $modifiers[] = 'unique()';
        }

        // Add index if specified
        if ($field->index) {
            $modifiers[] = 'index()';
        }

        // Add type-specific modifiers
        $typeModifiers = match ($this->type) {
            'integer', 'bigInteger' => ['unsigned()'],
            'boolean' => $field->default === null ? ['default(false)'] : [],
            default => []
        };

        return array_merge($modifiers, $typeModifiers);
    }

    /**
     * Get factory definition for the field
     */
    public function getFactoryDefinition(): string
    {
        return match ($this->type) {
            'integer' => 'fake()->randomNumber()',
            'string' => 'fake()->text(50)',
            'email' => 'fake()->safeEmail()',
            'url' => 'fake()->url()',
            'boolean' => 'fake()->boolean()',
            'date' => 'fake()->date()',
            'array' => '[]',
            default => 'fake()->text()',
        };
    }
}
