<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Adapters;

use Exception;
use Grazulex\LaravelModelschema\Contracts\FieldTypeInterface;
use Grazulex\LaravelModelschema\Support\FieldTypePluginManager;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\FieldTypeRegistry;
use InvalidArgumentException;

/**
 * Adapter for migrating TurboMaker field types to ModelSchema plugin system
 *
 * This adapter handles the transition from TurboMaker's field type system
 * to the plugin-based system in laravel-modelschema package.
 */
final class FieldTypeAdapter
{
    public function __construct(
        private FieldTypePluginManager $pluginManager
    ) {}

    /**
     * Register TurboMaker field types as plugins in ModelSchema
     */
    public function registerTurboFieldTypes(): void
    {
        $turboFieldTypes = FieldTypeRegistry::all();

        foreach ($turboFieldTypes as $typeName => $typeClass) {
            if (! $this->pluginManager->hasPlugin($typeName)) {
                $this->registerFieldTypeAsPlugin($typeClass);
            }
        }
    }

    /**
     * Check if a field type is available in either system
     */
    public function isFieldTypeAvailable(string $fieldType): bool
    {
        if ($this->pluginManager->hasPlugin($fieldType)) {
            return true;
        }

        return FieldTypeRegistry::has($fieldType);
    }

    /**
     * Get field type definition with fallback to TurboMaker system
     */
    public function getFieldTypeDefinition(string $fieldType): ?object
    {
        // Try ModelSchema plugin system first
        if ($this->pluginManager->hasPlugin($fieldType)) {
            return $this->pluginManager->getPlugin($fieldType);
        }

        // Fallback to TurboMaker system
        if (FieldTypeRegistry::has($fieldType)) {
            return FieldTypeRegistry::get($fieldType);
        }

        return null;
    }

    /**
     * Generate migration definition using appropriate field type system
     */
    public function generateMigrationDefinition(string $fieldType, array $field = []): string
    {
        // Try ModelSchema plugin system first
        if ($this->pluginManager->hasPlugin($fieldType)) {
            $plugin = $this->pluginManager->getPlugin($fieldType);
            if (method_exists($plugin, 'getMigrationDefinition')) {
                $fieldObject = $this->convertArrayToField($field);

                return $plugin->getMigrationDefinition($fieldObject);
            }
        }

        // Fallback to TurboMaker system
        if (FieldTypeRegistry::has($fieldType)) {
            $turboFieldType = FieldTypeRegistry::get($fieldType);
            $fieldObject = $this->convertArrayToField($field);

            return $turboFieldType->getMigrationDefinition($fieldObject);
        }

        throw new InvalidArgumentException("Field type '{$fieldType}' not found in either system");
    }

    /**
     * Generate validation rules using appropriate field type system
     */
    public function generateValidationRules(string $fieldType, array $field = []): array
    {
        // Try ModelSchema plugin system first
        if ($this->pluginManager->hasPlugin($fieldType)) {
            $plugin = $this->pluginManager->getPlugin($fieldType);
            if (method_exists($plugin, 'getValidationRules')) {
                // Pour ModelSchema, la méthode peut attendre un array plutôt qu'un objet
                return $plugin->getValidationRules($field);
            }
        }

        // Fallback to TurboMaker system
        if (FieldTypeRegistry::has($fieldType)) {
            $turboFieldType = FieldTypeRegistry::get($fieldType);
            $fieldObject = $this->convertArrayToField($field);

            return $turboFieldType->getValidationRules($fieldObject);
        }

        return [];
    }

    /**
     * Get all available field types from both systems
     */
    public function getAllAvailableFieldTypes(): array
    {
        $modelSchemaTypes = array_keys($this->pluginManager->getPlugins());
        $turboTypes = FieldTypeRegistry::getAvailableTypes();

        return array_merge($modelSchemaTypes, $turboTypes);
    }

    /**
     * Migrate specific field type from TurboMaker to ModelSchema plugin
     */
    public function migrateFieldType(string $fieldType): bool
    {
        if (! FieldTypeRegistry::has($fieldType)) {
            return false;
        }

        $turboFieldType = FieldTypeRegistry::get($fieldType);

        return $this->registerFieldTypeAsPlugin($turboFieldType::class);
    }

    /**
     * Check compatibility between TurboMaker and ModelSchema field types
     */
    public function checkFieldTypeCompatibility(string $fieldType): array
    {
        $compatibility = [
            'turbo_available' => FieldTypeRegistry::has($fieldType),
            'modelschema_available' => $this->pluginManager->hasPlugin($fieldType),
            'migration_needed' => false,
            'issues' => [],
        ];

        if ($compatibility['turbo_available'] && ! $compatibility['modelschema_available']) {
            $compatibility['migration_needed'] = true;
            $compatibility['issues'][] = 'Field type exists in TurboMaker but not in ModelSchema';
        }

        return $compatibility;
    }

    /**
     * Register a TurboMaker field type as a ModelSchema plugin
     */
    private function registerFieldTypeAsPlugin(string $typeClass): bool
    {
        try {
            // Convert TurboMaker field type to plugin format
            $pluginDefinition = $this->convertTurboFieldTypeToPlugin(new $typeClass());

            // Register with plugin manager
            $this->pluginManager->registerPlugin($pluginDefinition);

            return true;
        } catch (Exception $e) {
            // Log error or handle registration failure
            return false;
        }
    }

    /**
     * Convert TurboMaker field type instance to ModelSchema plugin format
     */
    private function convertTurboFieldTypeToPlugin(object $turboFieldType): FieldTypeInterface
    {
        // Pour l'instant, on retourne l'objet tel quel
        // TODO: Créer un wrapper si nécessaire
        return $turboFieldType;
    }

    /**
     * Convert array representation to Field object
     */
    private function convertArrayToField(array $fieldData): object
    {
        // Import the Field class
        $fieldClass = \Grazulex\LaravelTurbomaker\Schema\Field::class;

        // Create a basic Field object from array data
        $field = new $fieldClass(
            $fieldData['name'] ?? 'unknown',
            $fieldData['type'] ?? 'string',
            $fieldData['options'] ?? []
        );

        return $field;
    }
}
