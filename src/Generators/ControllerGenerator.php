<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class ControllerGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        $generated = [];

        // Generate web controller if not API only
        if (! ($context['options']['api_only'] ?? false)) {
            $webControllerPath = app_path("Http/Controllers/{$context['controller_class']}.php");
            $webStub = $this->getStub('controller');
            $webContent = $this->replaceTokens($webStub, $context);

            if ($this->writeFile($webControllerPath, $webContent, $context['options']['force'] ?? false)) {
                $generated[] = $webControllerPath;
            }
        }

        // Generate API controller
        $apiControllerPath = app_path("Http/Controllers/Api/{$context['controller_class']}.php");
        $apiStub = $this->getStub('controller.api');
        $apiContent = $this->replaceTokens($apiStub, $context);

        if ($this->writeFile($apiControllerPath, $apiContent, $context['options']['force'] ?? false)) {
            $generated[] = $apiControllerPath;
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Http\\Controllers';
    }
}
