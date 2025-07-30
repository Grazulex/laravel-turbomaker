<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class ObserverGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_observers'] ?? false)) {
            return [];
        }

        $generated = [];

        // Generate model observer
        $observerClass = $context['studly_name'].'Observer';
        $observerPath = app_path("Observers/{$observerClass}.php");
        $stub = $this->getStub('observer');

        $observerContext = array_merge($context, [
            'observer_class' => $observerClass,
        ]);

        $content = $this->replaceTokens($stub, $observerContext);

        if ($this->writeFile($observerPath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $observerPath;
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Observers';
    }
}
