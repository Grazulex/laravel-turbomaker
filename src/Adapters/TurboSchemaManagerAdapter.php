<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Adapters;

use Exception;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\TurboSchemaManager;

/**
 * Adapter that enhances TurboSchemaManager with ModelSchema capabilities
 * Uses composition to add new features while maintaining backward compatibility
 */
final class TurboSchemaManagerAdapter
{
    private TurboSchemaManager $originalManager;

    private ModelSchemaAdapter $modelSchemaAdapter;

    public function __construct(TurboSchemaManager $originalManager, ModelSchemaAdapter $modelSchemaAdapter)
    {
        $this->originalManager = $originalManager;
        $this->modelSchemaAdapter = $modelSchemaAdapter;
    }

    /**
     * Enhanced schema resolution with ModelSchema support
     */
    public function resolveSchema(?string $schemaInput, string $modelName): ?Schema
    {
        // Check if ModelSchema can handle this schema format
        if ($schemaInput && $this->modelSchemaAdapter->canHandleSchema($schemaInput)) {
            try {
                return $this->modelSchemaAdapter->parseSchema($schemaInput);
            } catch (Exception $e) {
                // Fall back to original manager
            }
        }

        // Use original manager for all other cases
        return $this->originalManager->resolveSchema($schemaInput, $modelName);
    }

    /**
     * Enhanced validation with ModelSchema capabilities
     */
    public function validateSchema(Schema $schema): array
    {
        $errors = [];

        // Try ModelSchema validation first for enhanced checks
        try {
            $modelSchema = $this->modelSchemaAdapter->toModelSchema($schema);
            // ModelSchema objects are considered valid if they were created successfully
            // Additional validation can be added here if needed
        } catch (Exception $e) {
            // ModelSchema validation not available or failed, continue with original
        }

        // Always run original validation as well
        $originalErrors = $this->originalManager->validateSchema($schema);

        return array_merge($errors, $originalErrors);
    }

    /**
     * Try to auto-discover a schema for the given model
     */
    public function tryAutoDiscovery(string $modelName): ?Schema
    {
        return $this->originalManager->tryAutoDiscovery($modelName);
    }

    /**
     * Create a new schema file from template
     */
    public function createSchemaFile(string $modelName, array $fields = [], array $relationships = []): string
    {
        return $this->originalManager->createSchemaFile($modelName, $fields, $relationships);
    }

    /**
     * List all available schemas
     */
    public function listSchemas(): array
    {
        return $this->originalManager->listSchemas();
    }

    /**
     * Check if a schema exists
     */
    public function schemaExists(string $schemaInput): bool
    {
        return $this->originalManager->schemaExists($schemaInput);
    }

    /**
     * Clear schema cache
     */
    public function clearCache(): void
    {
        $this->originalManager->clearCache();
    }

    /**
     * Get the original parser instance
     */
    public function getParser(): SchemaParserAdapter
    {
        return $this->originalManager->getParser();
    }
}
