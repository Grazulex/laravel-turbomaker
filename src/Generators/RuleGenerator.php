<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class RuleGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_rules'] ?? false)) {
            return [];
        }

        $generated = [];

        // Generate common validation rules for the model
        $rules = [
            'Exists'.$context['studly_name'].'Rule',
            'Unique'.$context['studly_name'].'Rule',
        ];

        foreach ($rules as $ruleClass) {
            $rulePath = app_path("Rules/{$ruleClass}.php");
            $ruleType = $this->getRuleType($ruleClass);
            $stub = $this->getStub("rule.{$ruleType}");

            $ruleContext = array_merge($context, [
                'rule_class' => $ruleClass,
                'rule_type' => $ruleType,
            ]);

            $content = $this->replaceTokens($stub, $ruleContext);

            if ($this->writeFile($rulePath, $content, $context['options']['force'] ?? false)) {
                $generated[] = $rulePath;
            }
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Rules';
    }

    private function getRuleType(string $ruleClass): string
    {
        if (str_contains($ruleClass, 'Exists')) {
            return 'exists';
        }
        if (str_contains($ruleClass, 'Unique')) {
            return 'unique';
        }

        return 'base';
    }
}
