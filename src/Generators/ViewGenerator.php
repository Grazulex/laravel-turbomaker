<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class ViewGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_views'] ?? true)) {
            return [];
        }

        $generated = [];
        $viewsDir = resource_path("views/{$context['kebab_name']}");

        $views = ['index', 'create', 'edit', 'show'];

        foreach ($views as $view) {
            $viewPath = "{$viewsDir}/{$view}.blade.php";
            $stub = $this->getStub("view.{$view}");
            $content = $this->replaceTokens($stub, $context);

            if ($this->writeFile($viewPath, $content, $context['options']['force'] ?? false)) {
                $generated[] = $viewPath;
            }
        }

        return $generated;
    }
}
