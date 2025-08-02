<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class ModelGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        $modelPath = app_path("Models/{$context['model_class']}.php");
        $stub = $this->getStub('model');
        $content = $this->replaceTokens($stub, $context);

        $generated = [];

        if ($this->writeFile($modelPath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $modelPath;
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Models';
    }

    protected function replaceTokens(string $content, array $context): string
    {
        // Get base tokens from parent
        $content = parent::replaceTokens($content, $context);

        // Add model-specific tokens
        $modelTokens = [
            '{{ schema_fillable_array }}' => $this->generateFillableArray($context),
            '{{ schema_casts_array }}' => $this->generateCastsArray($context),
            '{{ schema_relationships }}' => $this->generateRelationshipMethods($context),
            '{{ schema_traits }}' => $this->generateTraits($context),
            '{{ schema_uses }}' => $this->generateUses($context),
            '{{ model_traits }}' => $this->generateModelTraits($context),
        ];

        return str_replace(array_keys($modelTokens), array_values($modelTokens), $content);
    }

    private function generateFillableArray(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateFillableArray();
        }

        // Return empty string so fallback {{ fillable }} is used
        return '';
    }

    private function generateCastsArray(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateCastsArray();
        }

        // Default casts
        return "        'created_at' => 'datetime',\n        'updated_at' => 'datetime',";
    }

    private function generateRelationshipMethods(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateRelationshipMethods();
        }

        // Return empty string so fallback {{ relationships }} is used
        return '';
    }

    private function generateTraits(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateTraitsString();
        }

        return '';
    }

    private function generateUses(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateUsesString();
        }

        return '';
    }

    private function generateModelTraits(array $context): string
    {
        $traits = ['HasFactory'];

        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            $schemaUses = $context['schema']->generateUsesString();
            if ($schemaUses !== '' && $schemaUses !== '0') {
                $traits[] = $schemaUses;
            }
        }

        return implode(', ', $traits);
    }
}
