<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema;

final class Schema
{
    /**
     * @param  Field[]  $fields
     * @param  Relationship[]  $relationships
     */
    public function __construct(
        public readonly string $name,
        public readonly array $fields = [],
        public readonly array $relationships = [],
        public readonly array $options = [],
        public readonly array $metadata = [],
    ) {}

    /**
     * Create a Schema instance from array configuration
     */
    public static function fromArray(string $name, array $config): self
    {
        $fields = [];
        $relationships = [];

        // Parse fields
        foreach ($config['fields'] ?? [] as $fieldName => $fieldConfig) {
            $fields[$fieldName] = Field::fromArray($fieldName, $fieldConfig);
        }

        // Parse relationships
        foreach ($config['relationships'] ?? [] as $relationName => $relationConfig) {
            $relationships[$relationName] = Relationship::fromArray($relationName, $relationConfig);
        }

        return new self(
            name: $name,
            fields: $fields,
            relationships: $relationships,
            options: $config['options'] ?? [],
            metadata: $config['metadata'] ?? [],
        );
    }

    /**
     * Get all fields including foreign key fields from relationships
     */
    public function getAllFields(): array
    {
        $allFields = $this->fields;

        // Add foreign key fields from belongsTo relationships
        foreach ($this->relationships as $relationship) {
            $foreignKeyField = $relationship->getForeignKeyField();
            if ($foreignKeyField) {
                $allFields[$foreignKeyField->name] = $foreignKeyField;
            }
        }

        return $allFields;
    }

    /**
     * Get fillable fields for the model
     */
    public function getFillableFields(): array
    {
        return array_filter($this->getAllFields(), fn (Field $field): bool => $field->isFillable());
    }

    /**
     * Get fields that need casts in the model
     */
    public function getCastableFields(): array
    {
        $castable = [];

        foreach ($this->getAllFields() as $field) {
            $castType = $field->getCastType();
            if ($castType) {
                $castable[$field->name] = $castType;
            }
        }

        return $castable;
    }

    /**
     * Get validation rules for store request
     */
    public function getStoreValidationRules(): array
    {
        $rules = [];

        foreach ($this->getAllFields() as $field) {
            $fieldRules = $field->getValidationRules();
            if (! empty($fieldRules)) {
                $rules[$field->name] = $fieldRules;
            }
        }

        // Add relationship validation rules
        foreach ($this->relationships as $relationship) {
            $relationRules = $relationship->getValidationRules();
            if (! empty($relationRules)) {
                $foreignKey = $relationship->foreignKey ?? mb_strtolower($relationship->name).'_id';
                $rules[$foreignKey] = $relationRules;
            }
        }

        return $rules;
    }

    /**
     * Get validation rules for update request
     */
    public function getUpdateValidationRules(): array
    {
        $rules = $this->getStoreValidationRules();

        // Make non-required fields optional for updates
        foreach ($rules as &$fieldRules) {
            if (is_array($fieldRules) && in_array('required', $fieldRules)) {
                $fieldRules = array_filter($fieldRules, fn ($rule): bool => $rule !== 'required');
                array_unshift($fieldRules, 'sometimes');
            }
        }

        return $rules;
    }

    /**
     * Get migration fields (excluding auto-generated ones)
     */
    public function getMigrationFields(): array
    {
        return array_filter($this->getAllFields(), function (Field $field): bool {
            // Exclude auto-generated timestamp fields
            return ! in_array($field->name, ['id', 'created_at', 'updated_at', 'deleted_at']);
        });
    }

    /**
     * Get factory field definitions
     */
    public function getFactoryDefinitions(): array
    {
        $definitions = [];

        foreach ($this->getAllFields() as $field) {
            if ($field->isFillable()) {
                $definitions[$field->name] = $field->getFactoryDefinition();
            }
        }

        return $definitions;
    }

    /**
     * Get relationships by type
     */
    public function getRelationshipsByType(string $type): array
    {
        return array_filter($this->relationships, fn (Relationship $rel): bool => $rel->type === $type);
    }

    /**
     * Check if schema has timestamps
     */
    public function hasTimestamps(): bool
    {
        return $this->options['timestamps'] ?? true;
    }

    /**
     * Check if schema has soft deletes
     */
    public function hasSoftDeletes(): bool
    {
        return $this->options['soft_deletes'] ?? false;
    }

    /**
     * Get the table name
     */
    public function getTableName(): string
    {
        return $this->options['table'] ?? \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly($this->name));
    }

    /**
     * Get model traits
     */
    public function getModelTraits(): array
    {
        $traits = [];

        if ($this->hasSoftDeletes()) {
            $traits[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
        }

        // Add custom traits from options
        foreach ($this->options['traits'] ?? [] as $trait) {
            $traits[] = "use {$trait};";
        }

        return $traits;
    }

    /**
     * Get model uses (trait names only)
     */
    public function getModelUses(): array
    {
        $uses = [];

        if ($this->hasSoftDeletes()) {
            $uses[] = 'SoftDeletes';
        }

        // Add custom trait names from options
        foreach ($this->options['traits'] ?? [] as $trait) {
            $uses[] = class_basename($trait);
        }

        return $uses;
    }

    /**
     * Generate context for stub replacement
     */
    public function generateContext(): array
    {
        $fillableFields = $this->getFillableFields();
        $castableFields = $this->getCastableFields();

        return [
            'schema_fields' => $this->fields,
            'schema_relationships' => $this->relationships,
            'schema_fillable' => array_keys($fillableFields),
            'schema_casts' => $castableFields,
            'schema_table_name' => $this->getTableName(),
            'schema_has_timestamps' => $this->hasTimestamps(),
            'schema_has_soft_deletes' => $this->hasSoftDeletes(),
            'schema_traits' => $this->getModelTraits(),
            'schema_uses' => $this->getModelUses(),
            'schema_store_rules' => $this->getStoreValidationRules(),
            'schema_update_rules' => $this->getUpdateValidationRules(),
            'schema_factory_definitions' => $this->getFactoryDefinitions(),
            'schema_migration_fields' => $this->getMigrationFields(),
            'schema_options' => $this->options,
            'schema_metadata' => $this->metadata,
        ];
    }

    /**
     * Generate template tokens for stub replacement
     */
    public function generateTemplateTokens(): array
    {
        return [
            'schema_fillable_array' => $this->generateFillableArray(),
            'schema_casts_array' => $this->generateCastsArray(),
            'schema_relationships' => $this->generateRelationshipMethods(),
            'schema_migration_fields' => $this->generateMigrationFieldsString(),
            'schema_factory_definitions' => $this->generateFactoryDefinitionsString(),
            'schema_validation_rules' => $this->generateValidationRulesString(),
            'schema_resource_fields' => $this->generateResourceFieldsString(),
            'schema_traits' => $this->generateTraitsString(),
            'schema_uses' => $this->generateUsesString(),
        ];
    }

    /**
     * Generate the fillable array with proper formatting
     */
    public function generateFillableArray(): string
    {
        $fillable = array_keys($this->getFillableFields());
        if ($fillable === []) {
            return '';
        }

        return "\n        '".implode("',\n        '", $fillable)."',\n    ";
    }

    /**
     * Generate the casts array with proper formatting
     */
    public function generateCastsArray(): string
    {
        $casts = $this->getCastableFields();

        $lines = [];
        foreach ($casts as $field => $cast) {
            $lines[] = "        '{$field}' => '{$cast}',";
        }

        // Add default timestamps if enabled
        if ($this->hasTimestamps()) {
            $lines[] = "        'created_at' => 'datetime',";
            $lines[] = "        'updated_at' => 'datetime',";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate relationship methods string
     */
    public function generateRelationshipMethods(): string
    {
        if ($this->relationships === []) {
            return '';
        }

        $methods = [];
        foreach ($this->relationships as $relationship) {
            $methods[] = $relationship->generateMethod();
        }

        return "\n".implode("\n\n", $methods)."\n";
    }

    /**
     * Generate migration fields string
     */
    public function generateMigrationFieldsString(): string
    {
        $lines = ['            $table->id();'];

        foreach ($this->fields as $field) {
            $definition = $field->getMigrationDefinition();
            $lines[] = "            \$table->{$definition};";
        }

        // Add foreign key constraints from relationships
        foreach ($this->relationships as $relationship) {
            if ($constraint = $relationship->generateForeignKeyConstraint()) {
                $lines[] = $constraint;
            }
        }

        // Add soft deletes if enabled
        if ($this->hasSoftDeletes()) {
            $lines[] = '            $table->softDeletes();';
        }

        // Add timestamps if enabled
        if ($this->hasTimestamps()) {
            $lines[] = '            $table->timestamps();';
        }

        return implode("\n", $lines);
    }

    /**
     * Generate factory definitions string
     */
    public function generateFactoryDefinitionsString(): string
    {
        $definitions = [];

        foreach ($this->fields as $field) {
            if ($field->isFillable() && $definition = $field->getFactoryDefinition()) {
                $definitions[] = "            '{$field->name}' => {$definition},";
            }
        }

        return implode("\n", $definitions);
    }

    /**
     * Generate validation rules string
     */
    public function generateValidationRulesString(): string
    {
        $rules = [];
        $storeRules = $this->getStoreValidationRules();

        foreach ($storeRules as $field => $fieldRules) {
            $rulesArray = "'".implode("', '", $fieldRules)."'";
            $rules[] = "            '{$field}' => [{$rulesArray}],";
        }

        return implode("\n", $rules);
    }

    /**
     * Generate resource fields string
     */
    public function generateResourceFieldsString(): string
    {
        $fields = ["            'id' => \$this->id,"];

        foreach ($this->fields as $field) {
            if ($field->isFillable()) {
                $fields[] = "            '{$field->name}' => \$this->{$field->name},";
            }
        }

        if ($this->hasTimestamps()) {
            $fields[] = "            'created_at' => \$this->created_at,";
            $fields[] = "            'updated_at' => \$this->updated_at,";
        }

        return implode("\n", $fields);
    }

    /**
     * Generate traits import string
     */
    public function generateTraitsString(): string
    {
        $traits = $this->getModelTraits();

        return implode("\n", $traits);
    }

    /**
     * Generate uses string for traits
     */
    public function generateUsesString(): string
    {
        $uses = $this->getModelUses();

        return implode(', ', $uses);
    }
}
