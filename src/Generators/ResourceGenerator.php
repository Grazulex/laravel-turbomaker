<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class ResourceGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        $resourcePath = app_path("Http/Resources/{$context['resource_class']}.php");
        $stub = $this->getStub('resource');
        $content = $this->replaceTokens($stub, $context);

        $generated = [];

        if ($this->writeFile($resourcePath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $resourcePath;
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Http\\Resources';
    }

    protected function replaceTokens(string $content, array $context): string
    {
        // Get base tokens from parent
        $content = parent::replaceTokens($content, $context);

        // Add resource-specific tokens
        $resourceTokens = [
            '{{ schema_resource_fields }}' => $this->generateResourceFields($context),
        ];

        return str_replace(array_keys($resourceTokens), array_values($resourceTokens), $content);
    }

    private function generateResourceFields(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateResourceFieldsString();
        }

        // Fallback to basic resource fields
        return "            'id' => \$this->id,\n            'name' => \$this->name,\n            'created_at' => \$this->created_at,\n            'updated_at' => \$this->updated_at,";
    }
}
