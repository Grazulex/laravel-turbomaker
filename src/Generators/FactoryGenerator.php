<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class FactoryGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_factory'] ?? true)) {
            return [];
        }

        $factoryPath = database_path("factories/{$context['factory_class']}.php");
        $stub = $this->getStub('factory');
        $content = $this->replaceTokens($stub, $context);

        $generated = [];

        if ($this->writeFile($factoryPath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $factoryPath;
        }

        return $generated;
    }

    protected function replaceTokens(string $content, array $context): string
    {
        // Get base tokens from parent
        $content = parent::replaceTokens($content, $context);

        // Add factory-specific tokens
        $factoryTokens = [
            '{{ schema_factory_definitions }}' => $this->generateFactoryDefinitions($context),
        ];

        return str_replace(array_keys($factoryTokens), array_values($factoryTokens), $content);
    }

    private function generateFactoryDefinitions(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateFactoryDefinitionsString();
        }

        // Fallback to basic factory definition
        return "            'name' => fake()->name(),";
    }
}
