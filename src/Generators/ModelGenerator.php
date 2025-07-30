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
}
