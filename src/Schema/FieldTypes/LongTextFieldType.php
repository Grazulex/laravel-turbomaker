<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class LongTextFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'longText';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['string'];

        // Merge with custom rules
        $rules = array_merge($rules, $this->getCommonValidationRules($field));

        return array_unique($rules);
    }

    public function getFactoryDefinition(Field $field): string
    {
        // Check for custom factory rules first
        if ($field->factoryRules !== []) {
            return implode('->', $field->factoryRules);
        }

        // Generate longer content for longText
        return 'fake()->paragraphs(3, true)';
    }

    public function getCastType(Field $field): ?string
    {
        return null;
    }
}
