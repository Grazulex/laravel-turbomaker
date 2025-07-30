<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class TestGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_tests'] ?? true)) {
            return [];
        }

        $generated = [];

        $unitOnly = $context['options']['generate_unit_only'] ?? false;
        $featureOnly = $context['options']['generate_feature_only'] ?? false;

        // Generate Feature Test (unless unit only)
        if (! $unitOnly) {
            $featureTestPath = base_path("tests/Feature/{$context['test_feature_class']}.php");
            $featureStub = $this->getStub('test.feature');
            $featureContent = $this->replaceTokens($featureStub, $context);

            if ($this->writeFile($featureTestPath, $featureContent, $context['options']['force'] ?? false)) {
                $generated[] = $featureTestPath;
            }
        }

        // Generate Unit Test (unless feature only)
        if (! $featureOnly) {
            $unitTestPath = base_path("tests/Unit/{$context['test_unit_class']}.php");
            $unitStub = $this->getStub('test.unit');
            $unitContent = $this->replaceTokens($unitStub, $context);

            if ($this->writeFile($unitTestPath, $unitContent, $context['options']['force'] ?? false)) {
                $generated[] = $unitTestPath;
            }
        }

        return $generated;
    }
}
