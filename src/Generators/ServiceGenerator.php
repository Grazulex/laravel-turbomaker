<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class ServiceGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_services'] ?? false)) {
            return [];
        }

        $generated = [];

        // Generate main service class
        $serviceClass = $context['studly_name'].'Service';
        $servicePath = app_path("Services/{$serviceClass}.php");
        $stub = $this->getStub('service');

        $serviceContext = array_merge($context, [
            'service_class' => $serviceClass,
        ]);

        $content = $this->replaceTokens($stub, $serviceContext);

        if ($this->writeFile($servicePath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $servicePath;
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Services';
    }
}
