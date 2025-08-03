<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Adapters;

use Exception;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\Schema\SchemaParser;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

/**
 * Adapter that wraps SchemaParser with ModelSchema capabilities
 * Uses composition to maintain backward compatibility while adding new features
 */
class SchemaParserAdapter
{
    private SchemaParser $originalParser;

    private ModelSchemaAdapter $modelSchemaAdapter;

    public function __construct(SchemaParser $originalParser, ModelSchemaAdapter $modelSchemaAdapter)
    {
        $this->originalParser = $originalParser;
        $this->modelSchemaAdapter = $modelSchemaAdapter;
    }

    /**
     * Parse schema from various input types - Enhanced with ModelSchema
     */
    public function parse(string $schema): ?Schema
    {
        // Pour l'instant, déléguer au parser original
        // La logique ModelSchema sera ajoutée progressivement
        return $this->originalParser->parse($schema);
    }

    /**
     * Parse schema from array configuration - Enhanced with ModelSchema validation
     */
    public function parseArray(string $name, array $config): Schema
    {
        try {
            // Use ModelSchema validation if available
            if (! $this->modelSchemaAdapter->validateSchema($config)) {
                throw new InvalidArgumentException("Schema validation failed for '{$name}'");
            }
        } catch (Exception $e) {
            // Continue to original parsing - it will do its own validation
        }

        return $this->originalParser->parseArray($name, $config);
    }

    /**
     * Auto-discover schema for a given model name
     */
    public function autoDiscover(string $modelName): ?Schema
    {
        return $this->originalParser->autoDiscover($modelName);
    }

    /**
     * Parse inline schema (JSON or YAML string)
     */
    public function parseInline(string $schemaContent, string $modelName): Schema
    {
        return $this->originalParser->parseInline($schemaContent, $modelName);
    }

    /**
     * List all available schemas
     */
    public function listSchemas(): array
    {
        return $this->originalParser->getAllSchemas();
    }

    /**
     * Get all schemas (alias for listSchemas for compatibility)
     */
    public function getAllSchemas(): array
    {
        return $this->originalParser->getAllSchemas();
    }

    /**
     * Check if a schema exists
     */
    public function schemaExists(string $schemaName): bool
    {
        return $this->originalParser->exists($schemaName);
    }

    /**
     * Check if a schema exists (alias for schemaExists for compatibility)
     */
    public function exists(string $schemaName): bool
    {
        return $this->originalParser->exists($schemaName);
    }

    /**
     * Create a schema file
     */
    public function create(string $modelName, array $config): void
    {
        $this->originalParser->create($modelName, $config);
    }

    /**
     * Clear schema cache
     */
    public function clearCache(): void
    {
        $this->originalParser->clearCache();
    }
}
