<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Adapters;

use Grazulex\LaravelModelschema\Support\FieldTypePluginManager;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\FieldTypeRegistry as TurboFieldTypeRegistry;

/**
 * Adapter for migrating TurboMaker field types to ModelSchema plugin system
 * 
 * This adapter handles the transition from TurboMaker's field type system
 * to the plugin-based system in laravel-modelschema package.
 */
class FieldTypeAdapter
{
    public function __construct(
        private FieldTypePluginManager $pluginManager
    ) {
    }

    /**
     * Register TurboMaker field types as plugins in ModelSchema
     */
    public function registerTurboFieldTypes(): void
    {
        $turboFieldTypes = TurboFieldTypeRegistry::getAvailableTypes();
        
        foreach ($turboFieldTypes as $typeName => $typeClass) {
            if (!$this->pluginManager->hasPlugin($typeName)) {
                $this->registerFieldTypeAsPlugin($typeName, $typeClass);
            }
        }
    }

    /**
     * Check if a field type is available in either system
     */
    public function isFieldTypeAvailable(string $fieldType): bool
    {
        return $this->pluginManager->hasPlugin($fieldType) 
            || TurboFieldTypeRegistry::hasType($fieldType);
    }

    /**
     * Get field type definition with fallback to TurboMaker system
     */
    public function getFieldTypeDefinition(string $fieldType): ?array
    {
        // Try ModelSchema plugin system first
        if ($this->pluginManager->hasPlugin($fieldType)) {
            return $this->pluginManager->getPlugin($fieldType);
        }

        // Fallback to TurboMaker system
        if (TurboFieldTypeRegistry::hasType($fieldType)) {
            $turboFieldType = TurboFieldTypeRegistry::get($fieldType);
            return $this->convertTurboFieldTypeToPlugin($turboFieldType);
        }

        return null;
    }

    /**
     * Generate migration definition using appropriate field type system
     */
    public function generateMigrationDefinition(string $fieldType, array $options = []): string
    {
        // Try ModelSchema plugin system first
        if ($this->pluginManager->hasPlugin($fieldType)) {
            return $this->pluginManager->generateMigration($fieldType, $options);
        }

        // Fallback to TurboMaker system
        if (TurboFieldTypeRegistry::hasType($fieldType)) {
            $turboFieldType = TurboFieldTypeRegistry::get($fieldType);
            return $turboFieldType->getMigrationDefinition($options);
        }

        throw new \InvalidArgumentException("Field type '{$fieldType}' not found in either system");
    }

    /**
     * Generate validation rules using appropriate field type system
     */
    public function generateValidationRules(string $fieldType, array $options = []): array
    {
        // Try ModelSchema plugin system first
        if ($this->pluginManager->hasPlugin($fieldType)) {
            return $this->pluginManager->generateValidation($fieldType, $options);
        }

        // Fallback to TurboMaker system
        if (TurboFieldTypeRegistry::hasType($fieldType)) {
            $turboFieldType = TurboFieldTypeRegistry::get($fieldType);
            return $turboFieldType->getValidationRules($options);
        }

        return [];
    }

    /**
     * Get all available field types from both systems
     */
    public function getAllAvailableFieldTypes(): array
    {
        $modelSchemaTypes = $this->pluginManager->getAvailablePlugins();
        $turboTypes = TurboFieldTypeRegistry::getAvailableTypes();
        
        return array_merge($modelSchemaTypes, array_keys($turboTypes));
    }

    /**
     * Migrate specific field type from TurboMaker to ModelSchema plugin
     */
    public function migrateFieldType(string $fieldType): bool
    {
        if (!TurboFieldTypeRegistry::hasType($fieldType)) {
            return false;
        }

        $turboFieldType = TurboFieldTypeRegistry::get($fieldType);
        return $this->registerFieldTypeAsPlugin($fieldType, $turboFieldType::class);
    }

    /**
     * Check compatibility between TurboMaker and ModelSchema field types
     */
    public function checkFieldTypeCompatibility(string $fieldType): array
    {
        $compatibility = [
            'turbo_available' => TurboFieldTypeRegistry::hasType($fieldType),
            'modelschema_available' => $this->pluginManager->hasPlugin($fieldType),
            'migration_needed' => false,
            'issues' => [],
        ];

        if ($compatibility['turbo_available'] && !$compatibility['modelschema_available']) {
            $compatibility['migration_needed'] = true;
            $compatibility['issues'][] = "Field type exists in TurboMaker but not in ModelSchema";
        }

        return $compatibility;
    }

    /**
     * Register a TurboMaker field type as a ModelSchema plugin
     */
    private function registerFieldTypeAsPlugin(string $typeName, string $typeClass): bool
    {
        try {
            // Convert TurboMaker field type to plugin format
            $pluginDefinition = $this->convertTurboFieldTypeToPlugin(new $typeClass());
            
            // Register with plugin manager
            return $this->pluginManager->registerPlugin($typeName, $pluginDefinition);
        } catch (\Exception $e) {
            // Log error or handle registration failure
            return false;
        }
    }

    /**
     * Convert TurboMaker field type instance to ModelSchema plugin format
     */
    private function convertTurboFieldTypeToPlugin(object $turboFieldType): array
    {
        return [
            'name' => $turboFieldType->getName(),
            'migration_generator' => [$turboFieldType, 'getMigrationDefinition'],
            'validation_generator' => [$turboFieldType, 'getValidationRules'],
            'cast_type' => method_exists($turboFieldType, 'getCastType') 
                ? $turboFieldType->getCastType() 
                : null,
        ];
    }
}
