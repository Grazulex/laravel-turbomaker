<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema;

use InvalidArgumentException;

final class Relationship
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $model,
        public readonly ?string $foreignKey = null,
        public readonly ?string $localKey = null,
        public readonly ?string $pivotTable = null,
        public readonly array $pivotFields = [],
        public readonly bool $withTimestamps = false,
        public readonly array $attributes = [],
    ) {}

    /**
     * Create a Relationship instance from array configuration
     */
    public static function fromArray(string $name, array $config): self
    {
        return new self(
            name: $name,
            type: $config['type'],
            model: $config['model'],
            foreignKey: $config['foreign_key'] ?? null,
            localKey: $config['local_key'] ?? null,
            pivotTable: $config['pivot_table'] ?? null,
            pivotFields: $config['pivot_fields'] ?? [],
            withTimestamps: $config['with_timestamps'] ?? false,
            attributes: $config['attributes'] ?? [],
        );
    }

    /**
     * Get the model relationship method definition
     */
    public function getModelDefinition(): string
    {
        return match ($this->type) {
            'belongsTo' => $this->getBelongsToDefinition(),
            'hasOne' => $this->getHasOneDefinition(),
            'hasMany' => $this->getHasManyDefinition(),
            'belongsToMany' => $this->getBelongsToManyDefinition(),
            'morphTo' => $this->getMorphToDefinition(),
            'morphOne' => $this->getMorphOneDefinition(),
            'morphMany' => $this->getMorphManyDefinition(),
            default => throw new InvalidArgumentException("Unsupported relationship type: {$this->type}"),
        };
    }

    /**
     * Get the foreign key field that should be added to migration
     */
    public function getForeignKeyField(): ?Field
    {
        if ($this->type === 'belongsTo') {
            $foreignKey = $this->foreignKey ?? mb_strtolower($this->name).'_id';

            return new Field(
                name: $foreignKey,
                type: 'foreignId',
                nullable: false,
                attributes: ['constrained' => true]
            );
        }

        return null;
    }

    /**
     * Get factory relationship definition
     */
    public function getFactoryDefinition(): string
    {
        return match ($this->type) {
            'belongsTo' => $this->model.'::factory()',
            'hasOne', 'hasMany' => "// {$this->name} will be created separately",
            'belongsToMany' => "// {$this->name} will be attached separately",
            default => "// {$this->name} relationship",
        };
    }

    /**
     * Check if this relationship requires a migration constraint
     */
    public function requiresMigrationConstraint(): bool
    {
        return $this->type === 'belongsTo';
    }

    /**
     * Get the validation rules for this relationship's foreign key
     */
    public function getValidationRules(): array
    {
        if ($this->type !== 'belongsTo') {
            return [];
        }

        $rules = ['required', 'integer'];
        $tableName = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly(class_basename($this->model)));
        $rules[] = "exists:{$tableName},id";

        return $rules;
    }

    /**
     * Generate foreign key constraint for migration
     */
    public function generateForeignKeyConstraint(): ?string
    {
        if ($this->type !== 'belongsTo') {
            return null;
        }

        $foreignKey = $this->foreignKey ?? mb_strtolower($this->name).'_id';
        $referencedTable = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly(class_basename($this->model)));

        return "            \$table->foreignId('{$foreignKey}')->constrained('{$referencedTable}');";
    }

    /**
     * Generate complete relationship method for model
     */
    public function generateMethod(): string
    {
        $definition = $this->getModelDefinition();

        return "    public function {$this->name}()\n    {\n        {$definition}\n    }";
    }

    /**
     * Get belongsTo relationship definition
     */
    private function getBelongsToDefinition(): string
    {
        $modelClass = $this->formatModelClass($this->model);
        $params = [$modelClass.'::class'];

        if ($this->foreignKey !== null && $this->foreignKey !== '' && $this->foreignKey !== '0') {
            $params[] = "'{$this->foreignKey}'";
        }

        if ($this->localKey !== null && $this->localKey !== '' && $this->localKey !== '0') {
            $params[] = "'{$this->localKey}'";
        }

        return 'return $this->belongsTo('.implode(', ', $params).');';
    }

    /**
     * Get hasOne relationship definition
     */
    private function getHasOneDefinition(): string
    {
        $modelClass = $this->formatModelClass($this->model);
        $params = [$modelClass.'::class'];

        if ($this->foreignKey !== null && $this->foreignKey !== '' && $this->foreignKey !== '0') {
            $params[] = "'{$this->foreignKey}'";
        }

        if ($this->localKey !== null && $this->localKey !== '' && $this->localKey !== '0') {
            $params[] = "'{$this->localKey}'";
        }

        return 'return $this->hasOne('.implode(', ', $params).');';
    }

    /**
     * Get hasMany relationship definition
     */
    private function getHasManyDefinition(): string
    {
        $modelClass = $this->formatModelClass($this->model);
        $params = [$modelClass.'::class'];

        if ($this->foreignKey !== null && $this->foreignKey !== '' && $this->foreignKey !== '0') {
            $params[] = "'{$this->foreignKey}'";
        }

        if ($this->localKey !== null && $this->localKey !== '' && $this->localKey !== '0') {
            $params[] = "'{$this->localKey}'";
        }

        return 'return $this->hasMany('.implode(', ', $params).');';
    }

    /**
     * Get belongsToMany relationship definition
     */
    private function getBelongsToManyDefinition(): string
    {
        $modelClass = $this->formatModelClass($this->model);
        $params = [$modelClass.'::class'];

        if ($this->pivotTable !== null && $this->pivotTable !== '' && $this->pivotTable !== '0') {
            $params[] = "'{$this->pivotTable}'";
        }

        if ($this->foreignKey !== null && $this->foreignKey !== '' && $this->foreignKey !== '0') {
            $params[] = "'{$this->foreignKey}'";
        }

        if ($this->localKey !== null && $this->localKey !== '' && $this->localKey !== '0') {
            $params[] = "'{$this->localKey}'";
        }

        $definition = 'return $this->belongsToMany('.implode(', ', $params).')';

        // Add pivot fields
        if ($this->pivotFields !== []) {
            $pivotFields = "'".implode("', '", $this->pivotFields)."'";
            $definition .= "\n            ->withPivot([{$pivotFields}])";
        }

        // Add timestamps
        if ($this->withTimestamps) {
            $definition .= "\n            ->withTimestamps()";
        }

        return $definition.';';
    }

    /**
     * Get morphTo relationship definition
     */
    private function getMorphToDefinition(): string
    {
        $params = [];

        if ($this->foreignKey !== null && $this->foreignKey !== '' && $this->foreignKey !== '0') {
            $params[] = "'{$this->foreignKey}'";
        }

        return 'return $this->morphTo('.implode(', ', $params).');';
    }

    /**
     * Get morphOne relationship definition
     */
    private function getMorphOneDefinition(): string
    {
        $modelClass = $this->formatModelClass($this->model);
        $params = [$modelClass.'::class'];

        if ($this->foreignKey !== null && $this->foreignKey !== '' && $this->foreignKey !== '0') {
            $params[] = "'{$this->foreignKey}'";
        }

        return 'return $this->morphOne('.implode(', ', $params).');';
    }

    /**
     * Get morphMany relationship definition
     */
    private function getMorphManyDefinition(): string
    {
        $modelClass = $this->formatModelClass($this->model);
        $params = [$modelClass.'::class'];

        if ($this->foreignKey !== null && $this->foreignKey !== '' && $this->foreignKey !== '0') {
            $params[] = "'{$this->foreignKey}'";
        }

        return 'return $this->morphMany('.implode(', ', $params).');';
    }

    /**
     * Format model class name for PHP code generation
     */
    private function formatModelClass(string $modelClass): string
    {
        // If it already starts with a backslash, return as is
        if (str_starts_with($modelClass, '\\')) {
            return $modelClass;
        }

        // If it contains App\ but doesn't start with \, add the leading backslash
        if (str_starts_with($modelClass, 'App\\')) {
            return '\\'.$modelClass;
        }

        // If it's just a class name without namespace, assume App\Models
        if (! str_contains($modelClass, '\\')) {
            return '\\App\\Models\\'.$modelClass;
        }

        // Otherwise, add leading backslash to fully qualified class name
        return '\\'.$modelClass;
    }
}
