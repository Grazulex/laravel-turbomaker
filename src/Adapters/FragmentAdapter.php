<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Adapters;

use Grazulex\LaravelModelschema\Services\Generation\GenerationService;
use Grazulex\LaravelTurbomaker\Schema\Schema;

/**
 * Adapter for handling fragment generation and conversion
 * 
 * This adapter bridges TurboMaker's fragment system with the generation
 * capabilities of the laravel-modelschema package.
 */
class FragmentAdapter
{
    public function __construct(
        private GenerationService $generationService,
        private ModelSchemaAdapter $modelSchemaAdapter
    ) {
    }

    /**
     * Generate migration fragment using ModelSchema generation service
     */
    public function generateMigrationFragment(Schema $schema): string
    {
        $modelSchemaData = $this->modelSchemaAdapter->toModelSchema($schema);
        
        return $this->generationService->generateMigration($modelSchemaData);
    }

    /**
     * Generate model fragment using ModelSchema generation service
     */
    public function generateModelFragment(Schema $schema): string
    {
        $modelSchemaData = $this->modelSchemaAdapter->toModelSchema($schema);
        
        return $this->generationService->generateModel($modelSchemaData);
    }

    /**
     * Generate factory fragment using ModelSchema generation service
     */
    public function generateFactoryFragment(Schema $schema): string
    {
        $modelSchemaData = $this->modelSchemaAdapter->toModelSchema($schema);
        
        return $this->generationService->generateFactory($modelSchemaData);
    }

    /**
     * Generate validation rules fragment
     */
    public function generateValidationFragment(Schema $schema): array
    {
        $rules = [];
        
        foreach ($schema->fields as $field) {
            if (!empty($field->validationRules)) {
                $rules[$field->name] = $field->validationRules;
            }
        }
        
        return $rules;
    }

    /**
     * Generate fillable array fragment for model
     */
    public function generateFillableFragment(Schema $schema): array
    {
        $fillable = [];
        
        foreach ($schema->fields as $field) {
            // Skip certain field types that shouldn't be fillable
            if (!in_array($field->type, ['id', 'timestamps', 'created_at', 'updated_at'])) {
                $fillable[] = $field->name;
            }
        }
        
        return $fillable;
    }

    /**
     * Generate casts array fragment for model
     */
    public function generateCastsFragment(Schema $schema): array
    {
        $casts = [];
        
        foreach ($schema->fields as $field) {
            $cast = $this->getCastForFieldType($field->type);
            if ($cast !== null) {
                $casts[$field->name] = $cast;
            }
        }
        
        return $casts;
    }

    /**
     * Generate relationship methods fragment for model
     */
    public function generateRelationshipsFragment(Schema $schema): array
    {
        $relationships = [];
        
        foreach ($schema->relationships as $relationship) {
            $relationships[$relationship->name] = [
                'type' => $relationship->type,
                'model' => $relationship->model,
                'foreign_key' => $relationship->foreignKey,
                'local_key' => $relationship->localKey,
            ];
        }
        
        return $relationships;
    }

    /**
     * Convert TurboMaker fragments to ModelSchema format for processing
     */
    public function convertFragmentFormat(array $turboFragment): array
    {
        // Transform TurboMaker fragment structure to ModelSchema expected format
        return [
            'fields' => $turboFragment['fields'] ?? [],
            'relationships' => $turboFragment['relationships'] ?? [],
            'table' => $turboFragment['table'] ?? null,
            'model' => $turboFragment['model'] ?? null,
        ];
    }

    /**
     * Get appropriate cast type for field type
     */
    private function getCastForFieldType(string $fieldType): ?string
    {
        return match ($fieldType) {
            'boolean' => 'boolean',
            'integer', 'bigInteger', 'smallInteger', 'tinyInteger', 'mediumInteger', 'unsignedBigInteger' => 'integer',
            'decimal', 'double', 'float' => 'decimal:2',
            'json' => 'array',
            'date' => 'date',
            'dateTime', 'timestamp' => 'datetime',
            'time' => 'time',
            default => null,
        };
    }
}
