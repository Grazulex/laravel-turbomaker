<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

interface FieldTypeInterface
{
    /**
     * Get the migration column definition for this field type
     */
    public function getMigrationDefinition(Field $field): string;

    /**
     * Get type-specific validation rules
     */
    public function getValidationRules(Field $field): array;

    /**
     * Get factory definition for generating fake data
     */
    public function getFactoryDefinition(Field $field): string;

    /**
     * Get the Eloquent cast type for this field
     */
    public function getCastType(Field $field): ?string;

    /**
     * Get the migration column modifiers
     */
    public function getMigrationModifiers(Field $field): array;
}
