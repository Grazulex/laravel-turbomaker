<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class SeederGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_seeder'] ?? false)) {
            return [];
        }

        $seederPath = database_path("seeders/{$context['seeder_class']}.php");
        $stub = $this->getStub('seeder');
        $content = $this->replaceTokens($stub, $context);

        $generated = [];

        if ($this->writeFile($seederPath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $seederPath;
        }

        return $generated;
    }
}
