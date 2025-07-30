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
}
