<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

final class SchemaParser
{
    private string $schemasPath;

    private string $extension;

    private bool $cacheEnabled;

    private bool $autoDiscovery;

    private bool $validateOnLoad;

    public function __construct(?bool $cacheEnabled = null)
    {
        $this->schemasPath = config('turbomaker.schemas.path', resource_path('schemas'));
        $this->extension = config('turbomaker.schemas.extension', '.schema.yml');
        $this->cacheEnabled = $cacheEnabled ?? config('turbomaker.schemas.cache_enabled', true);
        $this->autoDiscovery = config('turbomaker.schemas.auto_discovery', true);
        $this->validateOnLoad = config('turbomaker.schemas.validate_on_load', true);
    }

    /**
     * Parse schema from file path or name
     */
    public function parse(string $schemaInput): ?Schema
    {
        $filePath = $this->resolveFilePath($schemaInput);

        if ($filePath === null || $filePath === '' || $filePath === '0' || ! File::exists($filePath)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($filePath);

        if ($this->shouldUseCache() && Cache::has($cacheKey)) {
            $config = Cache::get($cacheKey);
        } else {
            $config = $this->loadAndParseFile($filePath);

            if ($this->shouldUseCache()) {
                Cache::put($cacheKey, $config, now()->addHour());
            }
        }

        if ($this->validateOnLoad) {
            $this->validateSchema($config);
        }

        $schemaName = $this->extractSchemaName($schemaInput);

        return Schema::fromArray($schemaName, $config);
    }

    /**
     * Parse schema from inline YAML string
     */
    public function parseInline(string $name, string $yamlContent): Schema
    {
        $config = Yaml::parse($yamlContent);

        if ($this->validateOnLoad) {
            $this->validateSchema($config);
        }

        return Schema::fromArray($name, $config);
    }

    /**
     * Parse schema from array configuration
     */
    public function parseArray(string $name, array $config): Schema
    {
        if ($this->validateOnLoad) {
            $this->validateSchema($config);
        }

        return Schema::fromArray($name, $config);
    }

    /**
     * Auto-discover schema for a given model name
     */
    public function autoDiscover(string $modelName): ?Schema
    {
        if (! $this->autoDiscovery) {
            return null;
        }

        $schemaName = \Illuminate\Support\Str::snake($modelName);
        $filePath = $this->schemasPath.'/'.$schemaName.$this->extension;

        if (File::exists($filePath)) {
            return $this->parse($schemaName);
        }

        return null;
    }

    /**
     * Get all available schemas
     */
    public function getAllSchemas(): array
    {
        if (! File::exists($this->schemasPath)) {
            return [];
        }

        $schemas = [];
        $files = File::glob($this->schemasPath.'/*'.$this->extension);

        foreach ($files as $file) {
            $schemaName = $this->extractSchemaNameFromPath($file);
            $schemas[$schemaName] = $this->parse($schemaName);
        }

        return array_filter($schemas);
    }

    /**
     * Check if a schema exists
     */
    public function exists(string $schemaInput): bool
    {
        $filePath = $this->resolveFilePath($schemaInput);

        return $filePath && File::exists($filePath);
    }

    /**
     * Create a new schema file
     */
    public function create(string $name, array $config): bool
    {
        $filePath = $this->schemasPath.'/'.\Illuminate\Support\Str::snake($name).$this->extension;

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($filePath));

        $yamlContent = Yaml::dump($config, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        return File::put($filePath, $yamlContent) !== false;
    }

    /**
     * Clear schema cache
     */
    public function clearCache(): void
    {
        if ($this->shouldUseCache()) {
            // For simplicity, just clear all cache for now
            // In production, you might want to be more selective
            try {
                Cache::flush();
            } catch (Exception $e) {
                // Cache clearing failed, but that's not critical
                // Log the error if needed
            }
        }
    }

    /**
     * Resolve file path from input (file path or schema name)
     */
    private function resolveFilePath(string $input): ?string
    {
        // If it's already a full path and exists
        if (File::exists($input)) {
            return $input;
        }

        // If it's a relative path from schemas directory
        $relativePath = $this->schemasPath.'/'.mb_ltrim($input, '/');
        if (File::exists($relativePath)) {
            return $relativePath;
        }

        // If it's just a name, add extension
        $nameWithExtension = $this->schemasPath.'/'.$input.$this->extension;
        if (File::exists($nameWithExtension)) {
            return $nameWithExtension;
        }

        // Try snake case conversion
        $snakeCaseName = $this->schemasPath.'/'.\Illuminate\Support\Str::snake($input).$this->extension;
        if (File::exists($snakeCaseName)) {
            return $snakeCaseName;
        }

        return null;
    }

    /**
     * Load and parse YAML file
     */
    private function loadAndParseFile(string $filePath): array
    {
        try {
            $content = File::get($filePath);

            return Yaml::parse($content) ?? [];
        } catch (Exception $e) {
            throw new InvalidArgumentException("Failed to parse schema file {$filePath}: ".$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Extract schema name from input
     */
    private function extractSchemaName(string $input): string
    {
        $basename = basename($input);

        // Remove extension if present
        if (str_ends_with($basename, $this->extension)) {
            $basename = mb_substr($basename, 0, -mb_strlen($this->extension));
        }

        return \Illuminate\Support\Str::studly($basename);
    }

    /**
     * Extract schema name from file path
     */
    private function extractSchemaNameFromPath(string $filePath): string
    {
        $basename = basename($filePath, $this->extension);

        return \Illuminate\Support\Str::studly($basename);
    }

    /**
     * Generate cache key for schema file
     */
    private function getCacheKey(string $filePath): string
    {
        $lastModified = File::lastModified($filePath);

        return 'turbomaker.schema.'.md5($filePath.$lastModified);
    }

    /**
     * Check if cache should be used
     */
    private function shouldUseCache(): bool
    {
        if ($this->cacheEnabled === false) {
            return false;
        }

        // Vérifier l'environnement au moment de l'exécution
        if (app()->environment('testing')) {
            return false;
        }

        return $this->cacheEnabled;
    }

    /**
     * Validate schema configuration
     */
    private function validateSchema(array $config): void
    {
        // Validate fields
        if (isset($config['fields']) && ! is_array($config['fields'])) {
            throw new InvalidArgumentException('Schema fields must be an array');
        }

        foreach ($config['fields'] ?? [] as $fieldName => $fieldConfig) {
            $this->validateField($fieldName, $fieldConfig);
        }

        // Validate relationships
        if (isset($config['relationships']) && ! is_array($config['relationships'])) {
            throw new InvalidArgumentException('Schema relationships must be an array');
        }

        foreach ($config['relationships'] ?? [] as $relationName => $relationConfig) {
            $this->validateRelationship($relationName, $relationConfig);
        }
    }

    /**
     * Validate field configuration
     */
    private function validateField(string $fieldName, array $fieldConfig): void
    {
        if (! isset($fieldConfig['type'])) {
            throw new InvalidArgumentException("Field '{$fieldName}' must have a type");
        }

        $validTypes = [
            'string', 'text', 'integer', 'bigInteger', 'unsignedBigInteger', 'decimal', 'float', 'double',
            'boolean', 'date', 'datetime', 'timestamp', 'time', 'json', 'uuid',
            'email', 'url', 'foreignId', 'morphs',
        ];

        if (! in_array($fieldConfig['type'], $validTypes)) {
            throw new InvalidArgumentException("Invalid field type '{$fieldConfig['type']}' for field '{$fieldName}'");
        }
    }

    /**
     * Validate relationship configuration
     */
    private function validateRelationship(string $relationName, array $relationConfig): void
    {
        if (! isset($relationConfig['type'])) {
            throw new InvalidArgumentException("Relationship '{$relationName}' must have a type");
        }

        $validTypes = ['belongsTo', 'hasOne', 'hasMany', 'belongsToMany', 'morphTo', 'morphOne', 'morphMany'];

        if (! in_array($relationConfig['type'], $validTypes)) {
            throw new InvalidArgumentException("Invalid relationship type '{$relationConfig['type']}' for relationship '{$relationName}'");
        }

        if (! isset($relationConfig['model']) && $relationConfig['type'] !== 'morphTo') {
            throw new InvalidArgumentException("Relationship '{$relationName}' must have a model");
        }
    }
}
