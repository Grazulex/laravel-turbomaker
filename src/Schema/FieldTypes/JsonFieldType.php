<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use Grazulex\LaravelTurbomaker\Schema\Field;

final class JsonFieldType extends AbstractFieldType
{
    public function getMigrationDefinition(Field $field): string
    {
        return 'json';
    }

    public function getValidationRules(Field $field): array
    {
        $rules = ['json'];

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

        // Generate appropriate JSON based on field name
        $name = mb_strtolower($field->name);

        return match (true) {
            str_contains($name, 'meta') || str_contains($name, 'settings') => "['key1' => fake()->word(), 'key2' => fake()->sentence(), 'enabled' => fake()->boolean()]",
            str_contains($name, 'config') => "['theme' => fake()->colorName(), 'language' => fake()->languageCode(), 'timezone' => fake()->timezone()]",
            str_contains($name, 'attributes') || str_contains($name, 'properties') => "['color' => fake()->safeColorName(), 'size' => fake()->randomElement(['S', 'M', 'L', 'XL']), 'weight' => fake()->numberBetween(100, 5000)]",
            default => "['data' => fake()->words(3), 'value' => fake()->numberBetween(1, 100)]",
        };
    }

    public function getCastType(Field $field): ?string
    {
        return 'array';
    }
}
