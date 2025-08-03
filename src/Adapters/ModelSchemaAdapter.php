<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Adapters;

use Grazulex\LaravelModelschema\Services\SchemaService;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\Relationship;
use InvalidArgumentException;

/**
 * Adapter for bridging TurboMaker Schema with Laravel ModelSchema package
 * 
 * This adapter provides a seamless interface between TurboMaker's internal
 * schema system and the centralized laravel-modelschema package, allowing
 * for migration without breaking existing functionality.
 */
class ModelSchemaAdapter
{
    public function __construct(
        private ?SchemaService $schemaService = null
    ) {
        // SchemaService est optionnel pour permettre l'utilisation progressive
    }

    /**
     * Convert TurboMaker Schema to ModelSchema format
     */
    public function toModelSchema(Schema $turboSchema): array
    {
        $modelSchemaData = [
            'name' => $turboSchema->name,
            'table' => $turboSchema->getTableName(),
            'fields' => [],
            'relationships' => [],
        ];

        // Convert fields
        foreach ($turboSchema->fields as $field) {
            $modelSchemaData['fields'][$field->name] = $this->convertField($field);
        }

        // Convert relationships
        foreach ($turboSchema->relationships as $relationship) {
            $modelSchemaData['relationships'][$relationship->name] = $this->convertRelationship($relationship);
        }

        return $modelSchemaData;
    }

    /**
     * Convert ModelSchema format to TurboMaker Schema
     */
    public function fromModelSchema(array $modelSchemaData, string $schemaName): Schema
    {
        $fields = [];
        $relationships = [];
        
        // Convert fields
        if (isset($modelSchemaData['fields']) && is_array($modelSchemaData['fields'])) {
            foreach ($modelSchemaData['fields'] as $fieldName => $fieldData) {
                $fields[$fieldName] = $this->convertModelSchemaField($fieldName, $fieldData);
            }
        }

        // Convert relationships
        if (isset($modelSchemaData['relationships']) && is_array($modelSchemaData['relationships'])) {
            foreach ($modelSchemaData['relationships'] as $relationshipName => $relationshipData) {
                $relationships[$relationshipName] = $this->convertModelSchemaRelationship($relationshipName, $relationshipData);
            }
        }

        return new Schema(
            name: $schemaName,
            fields: $fields,
            relationships: $relationships,
            options: $modelSchemaData['options'] ?? []
        );
    }

    /**
     * Parse a schema file using ModelSchema format
     */
    public function parseSchema(string $schemaPath): ?Schema
    {
        if (!$this->schemaService) {
            throw new \RuntimeException('SchemaService not available. ModelSchema integration not fully initialized.');
        }

        try {
            $modelSchema = $this->schemaService->parseSchema($schemaPath);
            return $this->convertFromModelSchema($modelSchema);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Failed to parse schema at '{$schemaPath}': " . $e->getMessage());
        }
    }

    /**
     * Validate schema using ModelSchema service
     */
    public function validateSchema(array $schemaData): bool
    {
        if (!$this->schemaService) {
            // Si ModelSchema n'est pas disponible, on fait une validation basique
            $required = ['name'];
            foreach ($required as $field) {
                if (!isset($schemaData[$field])) {
                    return false;
                }
            }
            return true;
        }

        try {
            // For now, we'll do basic validation since ModelSchema expects a ModelSchema object
            // A future improvement would be to convert the array to ModelSchema object first
            $required = ['name'];
            foreach ($required as $field) {
                if (!isset($schemaData[$field])) {
                    return false;
                }
            }
            
            // Basic field validation
            if (isset($schemaData['fields']) && is_array($schemaData['fields'])) {
                foreach ($schemaData['fields'] as $fieldName => $fieldData) {
                    if (!is_array($fieldData) || !isset($fieldData['type'])) {
                        return false;
                    }
                }
            }
            
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Convert TurboMaker Field to ModelSchema field format
     */
    private function convertField(Field $field): array
    {
        $fieldData = [
            'type' => $field->type,
            'nullable' => $field->nullable,
        ];

        // Add additional properties
        if ($field->length !== null) {
            $fieldData['length'] = $field->length;
        }

        if ($field->default !== null) {
            $fieldData['default'] = $field->default;
        }

        if ($field->comment !== null) {
            $fieldData['comment'] = $field->comment;
        }

        // Add validation rules if present
        if (!empty($field->validationRules)) {
            $fieldData['validation'] = $field->validationRules;
        }

        return $fieldData;
    }

    /**
     * Convert TurboMaker Relationship to ModelSchema relationship format
     */
    private function convertRelationship(Relationship $relationship): array
    {
        return [
            'type' => $relationship->type,
            'model' => $relationship->model,
            'foreign_key' => $relationship->foreignKey,
            'local_key' => $relationship->localKey,
        ];
    }

    /**
     * Convert ModelSchema field format to TurboMaker Field
     */
    private function convertModelSchemaField(string $fieldName, array $fieldData): Field
    {
        return new Field(
            name: $fieldName,
            type: $fieldData['type'] ?? 'string',
            nullable: $fieldData['nullable'] ?? false,
            unique: $fieldData['unique'] ?? false,
            index: $fieldData['index'] ?? false,
            default: $fieldData['default'] ?? null,
            length: isset($fieldData['length']) ? (int) $fieldData['length'] : null,
            comment: $fieldData['comment'] ?? null,
            attributes: $fieldData['attributes'] ?? [],
            validationRules: $fieldData['validation'] ?? [],
            factoryRules: $fieldData['factory'] ?? []
        );
    }

    /**
     * Convert ModelSchema relationship format to TurboMaker Relationship
     */
    private function convertModelSchemaRelationship(string $relationshipName, array $relationshipData): Relationship
    {
        return new Relationship(
            $relationshipName,
            $relationshipData['type'] ?? 'belongsTo',
            $relationshipData['model'] ?? '',
            $relationshipData['foreign_key'] ?? null,
            $relationshipData['local_key'] ?? null
        );
    }
}
