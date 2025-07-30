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
}
