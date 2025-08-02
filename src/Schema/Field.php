<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema;

use Grazulex\LaravelTurbomaker\Schema\FieldTypes\FieldTypeRegistry;

final class Field
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $nullable = false,
        public readonly bool $unique = false,
        public readonly bool $index = false,
        public readonly mixed $default = null,
        public readonly ?int $length = null,
        public readonly ?string $comment = null,
        public readonly array $attributes = [],
        public readonly array $validationRules = [],
        public readonly array $factoryRules = [],
    ) {}

    /**
     * Create a Field instance from array configuration
     */
    public static function fromArray(string $name, array $config): self
    {
        return new self(
            name: $name,
            type: $config['type'] ?? 'string',
            nullable: $config['nullable'] ?? false,
            unique: $config['unique'] ?? false,
            index: $config['index'] ?? false,
            default: $config['default'] ?? null,
            length: isset($config['length']) ? (int) $config['length'] : null,
            comment: $config['comment'] ?? null,
            attributes: $config['attributes'] ?? [],
            validationRules: $config['validation'] ?? [],
            factoryRules: $config['factory'] ?? [],
        );
    }

    /**
     * Get the migration column definition
     */
    public function getMigrationDefinition(): string
    {
        $fieldType = FieldTypeRegistry::get($this->type);

        return $fieldType->getMigrationDefinition($this);
    }

    /**
     * Get migration column modifiers
     */
    public function getMigrationModifiers(): array
    {
        $fieldType = FieldTypeRegistry::get($this->type);

        return $fieldType->getMigrationModifiers($this);
    }

    /**
     * Get validation rules for requests
     */
    public function getValidationRules(?string $tableName = null): array
    {
        $fieldType = FieldTypeRegistry::get($this->type);
        $rules = $fieldType->getValidationRules($this);

        // Add table-specific unique constraint if needed
        if ($this->unique && $tableName) {
            // Remove any existing unique rule and add table-specific one
            $rules = array_filter($rules, function ($rule): bool {
                return ! str_starts_with($rule, 'unique:');
            });
            $rules[] = "unique:{$tableName},{$this->name}";
        }

        return array_values(array_unique($rules));
    }

    /**
     * Get factory definition
     */
    public function getFactoryDefinition(): string
    {
        $fieldType = FieldTypeRegistry::get($this->type);

        return $fieldType->getFactoryDefinition($this);
    }

    /**
     * Get the model fillable attribute
     */
    public function isFillable(): bool
    {
        // By default, all fields except timestamps and primary keys are fillable
        return ! in_array($this->name, ['id', 'created_at', 'updated_at', 'deleted_at']);
    }

    /**
     * Get the model cast attribute
     */
    public function getCastType(): ?string
    {
        $fieldType = FieldTypeRegistry::get($this->type);

        return $fieldType->getCastType($this);
    }
}
