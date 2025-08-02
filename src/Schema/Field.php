<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema;

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
        // Special handling for unsignedBigInteger
        $type = match ($this->type) {
            'unsignedBigInteger' => 'unsignedBigInteger',
            default => $this->type,
        };

        return $type;
    }

    /**
     * Get migration column modifiers
     */
    public function getMigrationModifiers(): array
    {
        $modifiers = [];

        // Handle length for string types
        if ($this->length && in_array($this->type, ['string', 'char'])) {
            // This will be handled in the main definition
        }

        if ($this->nullable) {
            $modifiers[] = 'nullable()';
        }

        if ($this->unique) {
            $modifiers[] = 'unique()';
        }

        if ($this->index) {
            $modifiers[] = 'index()';
        }

        if ($this->default !== null) {
            $defaultValue = is_string($this->default) ? "'{$this->default}'" : $this->default;
            $modifiers[] = "default({$defaultValue})";
        }

        if ($this->comment !== null && $this->comment !== '' && $this->comment !== '0') {
            $modifiers[] = "comment('{$this->comment}')";
        }

        return $modifiers;
    }

    /**
     * Get validation rules for requests
     */
    public function getValidationRules(string $tableName = null): array
    {
        $rules = [];

        // Base required rule
        $rules[] = $this->nullable ? 'nullable' : 'required';

        // Type-specific rules
        match ($this->type) {
            'string', 'text' => $rules[] = 'string',
            'integer', 'bigInteger', 'unsignedBigInteger' => $rules[] = 'integer',
            'decimal', 'float', 'double' => $rules[] = 'numeric',
            'boolean' => $rules[] = 'boolean',
            'date' => $rules[] = 'date',
            'datetime', 'timestamp' => $rules[] = 'date',
            'email' => $rules = array_merge($rules, ['string', 'email']),
            'url' => $rules = array_merge($rules, ['string', 'url']),
            default => null,
        };

        // Length constraints
        if ($this->length && in_array($this->type, ['string', 'text'])) {
            $rules[] = "max:{$this->length}";
        }

        // Unique constraint
        if ($this->unique) {
            $table = $tableName ?: '{{table_name}}';
            $rules[] = "unique:{$table},{$this->name}";
        }

        // Custom validation rules - merge intelligently to avoid duplicates
        if ($this->validationRules !== []) {
            // Merge custom rules but avoid conflicts
            foreach ($this->validationRules as $customRule) {
                // Check for rule conflicts (e.g., multiple max: rules)
                $ruleName = explode(':', $customRule)[0];
                
                // Remove existing rule of same type to avoid conflicts
                $rules = array_filter($rules, function ($rule) use ($ruleName) {
                    return ! str_starts_with($rule, $ruleName.':');
                });
                
                $rules[] = $customRule;
            }
        }

        // Remove exact duplicates while preserving order
        return array_values(array_unique($rules));
    }

    /**
     * Get factory definition
     */
    public function getFactoryDefinition(): string
    {
        // Custom factory rules take precedence
        if ($this->factoryRules !== []) {
            return implode('->', $this->factoryRules);
        }

        // Default factory based on type and name
        return match ($this->type) {
            'string' => $this->getStringFactoryDefinition(),
            'text' => 'fake()->paragraph()',
            'integer' => 'fake()->numberBetween(1, 1000)',
            'bigInteger', 'unsignedBigInteger' => 'fake()->numberBetween(1, 999999)',
            'decimal', 'float', 'double' => 'fake()->randomFloat(2, 0, 999)',
            'boolean' => 'fake()->boolean()',
            'date' => 'fake()->date()',
            'datetime', 'timestamp' => 'fake()->dateTime()',
            'email' => 'fake()->unique()->safeEmail()',
            'url' => 'fake()->url()',
            default => 'null',
        };
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
        return match ($this->type) {
            'boolean' => 'boolean',
            'integer', 'bigInteger', 'unsignedBigInteger' => 'integer',
            'decimal', 'float', 'double' => 'decimal:2',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'json' => 'array',
            default => null,
        };
    }

    /**
     * Get string factory definition based on field name
     */
    private function getStringFactoryDefinition(): string
    {
        $name = mb_strtolower($this->name);

        return match (true) {
            str_contains($name, 'email') => 'fake()->unique()->safeEmail()',
            str_contains($name, 'name') && str_contains($name, 'first') => 'fake()->firstName()',
            str_contains($name, 'name') && str_contains($name, 'last') => 'fake()->lastName()',
            str_contains($name, 'name') => 'fake()->name()',
            str_contains($name, 'phone') => 'fake()->phoneNumber()',
            str_contains($name, 'address') => 'fake()->address()',
            str_contains($name, 'city') => 'fake()->city()',
            str_contains($name, 'country') => 'fake()->country()',
            str_contains($name, 'title') => 'fake()->sentence(3)',
            str_contains($name, 'slug') => 'fake()->slug()',
            str_contains($name, 'uuid') => 'fake()->uuid()',
            $this->length && $this->length <= 50 => 'fake()->word()',
            default => $this->length !== null && $this->length !== 0 ? "fake()->text({$this->length})" : 'fake()->sentence()',
        };
    }
}
