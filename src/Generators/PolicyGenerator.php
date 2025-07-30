<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class PolicyGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_policies'] ?? false)) {
            return [];
        }

        $policyPath = app_path("Policies/{$context['policy_class']}.php");
        $stub = $this->getStub('policy');
        $content = $this->replaceTokens($stub, $context);

        $generated = [];

        if ($this->writeFile($policyPath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $policyPath;
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Policies';
    }
}
