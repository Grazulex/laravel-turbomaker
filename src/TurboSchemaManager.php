<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker;

use Exception;
use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaAdapter;
use Grazulex\LaravelTurbomaker\Adapters\SchemaParserAdapter;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\Schema\SchemaParser;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class TurboSchemaManager
{
    private SchemaParserAdapter $parser;

    public function __construct()
    {
        // Utiliser le nouvel adapter qui combine TurboMaker et ModelSchema
        $originalParser = new SchemaParser(false); // Toujours désactiver le cache

        // Créer ModelSchemaAdapter - SchemaService sera injecté quand disponible
        $modelSchemaAdapter = new ModelSchemaAdapter();

        $this->parser = new SchemaParserAdapter($originalParser, $modelSchemaAdapter);
    }

    /**
     * Resolve schema from various input types - Enhanced with ModelSchema
     */
    public function resolveSchema(?string $schemaInput, string $modelName): ?Schema
    {
        // No schema input provided
        if ($schemaInput === null || $schemaInput === '' || $schemaInput === '0') {
            return $this->tryAutoDiscovery($modelName);
        }

        // Try ModelSchema parsing first for enhanced formats
        if ($this->canUseModelSchema($schemaInput)) {
            try {
                $modelSchemaAdapter = new ModelSchemaAdapter();

                return $modelSchemaAdapter->parseSchema($schemaInput);
            } catch (Exception $e) {
                // Fall back to original parsing
            }
        }

        // Handle inline schema definition (JSON or YAML)
        if ($this->isInlineSchema($schemaInput)) {
            return $this->parseInlineSchema($schemaInput, $modelName);
        }

        // Handle fields shorthand (e.g., --fields="name:string,email:email,age:integer")
        if ($this->isFieldsShorthand($schemaInput)) {
            return $this->parseFieldsShorthand($schemaInput, $modelName);
        }

        // Handle file path or schema name
        return $this->parser->parse($schemaInput);
    }

    /**
     * Try to auto-discover a schema for the given model
     */
    public function tryAutoDiscovery(string $modelName): ?Schema
    {
        return $this->parser->autoDiscover($modelName);
    }

    /**
     * Validate schema before using it - Enhanced with ModelSchema capabilities
     */
    public function validateSchema(Schema $schema): array
    {
        $errors = [];

        // Try enhanced ModelSchema validation if available
        try {
            $modelSchemaAdapter = new ModelSchemaAdapter();
            $modelSchema = $modelSchemaAdapter->toModelSchema($schema);
            // ModelSchema objects are considered valid if they were created successfully
            // Additional validation can be added here if needed
        } catch (Exception $e) {
            // ModelSchema validation not available or failed, continue with original validation
        }

        // Always run original validation as well
        $originalErrors = $this->validateSchemaOriginal($schema);

        return array_merge($errors, $originalErrors);
    }

    /**
     * Get schema parser instance
     */
    public function getParser(): SchemaParserAdapter
    {
        return $this->parser;
    }

    /**
     * Create a new schema file from template
     */
    public function createSchemaFile(string $modelName, array $fields = [], array $relationships = []): string
    {
        $schemaConfig = [
            'fields' => $fields,
            'relationships' => $relationships,
            'options' => [
                'table' => \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly($modelName)),
                'timestamps' => true,
                'soft_deletes' => false,
            ],
            'metadata' => [
                'version' => '1.0',
                'description' => "Schema for {$modelName} model",
                'created_at' => now()->toDateString(),
            ],
        ];

        $this->parser->create($modelName, $schemaConfig);

        $schemaPath = config('turbomaker.schemas.path', resource_path('schemas'));
        $fileName = \Illuminate\Support\Str::snake($modelName).config('turbomaker.schemas.extension', '.schema.yml');

        return $schemaPath.'/'.$fileName;
    }

    /**
     * List all available schemas
     */
    public function listSchemas(): array
    {
        return $this->parser->getAllSchemas();
    }

    /**
     * Check if a schema exists
     */
    public function schemaExists(string $schemaInput): bool
    {
        return $this->parser->exists($schemaInput);
    }

    /**
     * Clear schema cache
     */
    public function clearCache(): void
    {
        $this->parser->clearCache();
    }

    /**
     * Check if we can use ModelSchema for this input
     */
    private function canUseModelSchema(string $schemaInput): bool
    {
        $modelSchemaAdapter = new ModelSchemaAdapter();

        return $modelSchemaAdapter->canHandleSchema($schemaInput);
    }

    /**
     * Original validation logic (for compatibility and fallback)
     */
    private function validateSchemaOriginal(Schema $schema): array
    {
        $errors = [];

        // Check for required fields
        if ($schema->fields === []) {
            $errors[] = 'Schema must have at least one field';
        }

        // Validate field types
        foreach ($schema->fields as $field) {
            if (! $this->isValidFieldType($field->type)) {
                $errors[] = "Invalid field type '{$field->type}' for field '{$field->name}'";
            }
        }

        // Validate relationships
        foreach ($schema->relationships as $relationship) {
            if (! $this->isValidRelationshipType($relationship->type)) {
                $errors[] = "Invalid relationship type '{$relationship->type}' for relationship '{$relationship->name}'";
            }
        }

        return $errors;
    }

    /**
     * Check if input is inline schema (starts with { or fields:)
     */
    private function isInlineSchema(string $input): bool
    {
        $trimmed = mb_trim($input);

        return str_starts_with($trimmed, '{') || str_starts_with($trimmed, 'fields:');
    }

    /**
     * Check if input is fields shorthand (contains : but not yaml structure)
     */
    private function isFieldsShorthand(string $input): bool
    {
        return str_contains($input, ':') &&
               ! str_contains($input, 'fields:') &&
               ! str_starts_with(mb_trim($input), '{');
    }

    /**
     * Parse inline schema (JSON or YAML)
     */
    private function parseInlineSchema(string $input, string $modelName): Schema
    {
        $trimmed = mb_trim($input);

        if (str_starts_with($trimmed, '{')) {
            // JSON format
            $config = json_decode($trimmed, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON in inline schema: '.json_last_error_msg());
            }

            return $this->parser->parseArray($modelName, $config);
        }

        // YAML format
        return $this->parser->parseInline($modelName, $trimmed);

    }

    /**
     * Parse fields shorthand into schema
     * Format: "name:string,email:email,age:integer:nullable,price:decimal:2,8"
     */
    private function parseFieldsShorthand(string $input, string $modelName): Schema
    {
        $config = ['fields' => []];
        $fieldsInput = explode(',', $input);

        foreach ($fieldsInput as $fieldDefinition) {
            $parts = explode(':', mb_trim($fieldDefinition));

            if (count($parts) < 2) {
                throw new InvalidArgumentException("Invalid field definition: {$fieldDefinition}. Expected format: name:type[:options]");
            }

            $fieldName = mb_trim($parts[0]);
            $fieldType = mb_trim($parts[1]);
            $options = array_slice($parts, 2);

            $fieldConfig = [
                'type' => $fieldType,
                'nullable' => false,
            ];

            // Parse options
            foreach ($options as $option) {
                $option = mb_trim($option);

                if ($option === 'nullable') {
                    $fieldConfig['nullable'] = true;
                } elseif ($option === 'unique') {
                    $fieldConfig['unique'] = true;
                } elseif ($option === 'index') {
                    $fieldConfig['index'] = true;
                } elseif (is_numeric($option)) {
                    // Length for strings or precision for decimals
                    if (! isset($fieldConfig['length'])) {
                        $fieldConfig['length'] = (int) $option;
                    } else {
                        // For decimal types: precision,scale
                        $fieldConfig['length'] = $fieldConfig['length'].','.$option;
                    }
                } elseif (str_starts_with($option, 'default:')) {
                    $defaultValue = mb_substr($option, 8);
                    $fieldConfig['default'] = $this->parseDefaultValue($defaultValue);
                }
            }

            $config['fields'][$fieldName] = $fieldConfig;
        }

        return $this->parser->parseArray($modelName, $config);
    }

    /**
     * Parse default value with proper type casting
     */
    private function parseDefaultValue(string $value): mixed
    {
        // Handle boolean values
        if (in_array(mb_strtolower($value), ['true', 'false'])) {
            return mb_strtolower($value) === 'true';
        }

        // Handle null
        if (mb_strtolower($value) === 'null') {
            return null;
        }

        // Handle numeric values
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        // Remove quotes if present
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return mb_substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Check if field type is valid
     */
    private function isValidFieldType(string $type): bool
    {
        // Use the FieldTypeRegistry to check if type is registered
        $registry = app(\Grazulex\LaravelTurbomaker\Schema\FieldTypes\FieldTypeRegistry::class);

        return $registry->has($type);
    }

    /**
     * Check if relationship type is valid
     */
    private function isValidRelationshipType(string $type): bool
    {
        $validTypes = ['belongsTo', 'hasOne', 'hasMany', 'belongsToMany', 'morphTo', 'morphOne', 'morphMany'];

        return in_array($type, $validTypes);
    }
}
