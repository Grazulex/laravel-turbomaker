<?php

declare(strict_types=1);

namespace App\TurboMaker\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\AbstractFieldType;

/**
 * Custom Money field type - Example of how to extend the system
 *
 * This field type handles monetary values with proper validation,
 * factory generation, and casting.
 */
final class MoneyFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'decimal';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['numeric', 'min:0'];

        // Add scale validation if specified
        if (isset($field->attributes['scale'])) {
            $scale = $field->attributes['scale'];
            $rules[] = "regex:/^\d+(\.\d{1,{$scale}})?$/";
        }

        // Max amount validation
        if (isset($field->attributes['max_amount'])) {
            $rules[] = "max:{$field->attributes['max_amount']}";
        }

        // Merge with common validation rules
        $rules = array_merge($rules, $this->getCommonValidationRules($field));

        return array_unique($rules);
    }

    public function getFactoryDefinition(Field $field): string
    {
        // Check for custom factory rules first
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        // Generate realistic money amounts
        $minAmount = $field->attributes['min_amount'] ?? 10;
        $maxAmount = $field->attributes['max_amount'] ?? 9999;
        $scale = $field->attributes['scale'] ?? 2;

        return "fake()->randomFloat({$scale}, {$minAmount}, {$maxAmount})";
    }

    public function getCastType(Field $field): ?string
    {
        $precision = $field->attributes['precision'] ?? 8;
        $scale = $field->attributes['scale'] ?? 2;

        return "decimal:{$scale}";
    }

    public function getMigrationModifiers(Field $field): array
    {
        $modifiers = parent::getMigrationModifiers($field);

        // Add precision and scale for decimal columns
        $precision = $field->attributes['precision'] ?? 8;
        $scale = $field->attributes['scale'] ?? 2;

        // The decimal definition should include precision and scale
        // This will be handled in the migration generation where it becomes:
        // $table->decimal('price', 8, 2)->nullable()...

        return $modifiers;
    }
}
