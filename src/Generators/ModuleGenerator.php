<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final class ModuleGenerator
{
    private Filesystem $files;

    private array $generators;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->initializeGenerators();
    }

    public function generate(string $name, array $options = []): array
    {
        $generatedFiles = [];
        $context = $this->buildContext($name, $options);

        // Generate in order of dependencies
        $generationOrder = [
            'migration',
            'model',
            'factory',
            'seeder',
            'policy',
            'rules',
            'observers',
            'request',
            'resource',
            'actions',
            'services',
            'controller',
            'routes',
            'views',
            'tests',
        ];

        foreach ($generationOrder as $type) {
            if ($this->shouldGenerate($type, $options)) {
                $files = $this->generators[$type]->generate($context);
                $generatedFiles[$type] = $files;
            }
        }

        return $generatedFiles;
    }

    private function initializeGenerators(): void
    {
        $this->generators = [
            'migration' => new MigrationGenerator($this->files),
            'model' => new ModelGenerator($this->files),
            'factory' => new FactoryGenerator($this->files),
            'seeder' => new SeederGenerator($this->files),
            'policy' => new PolicyGenerator($this->files),
            'rules' => new RuleGenerator($this->files),
            'observers' => new ObserverGenerator($this->files),
            'request' => new RequestGenerator($this->files),
            'resource' => new ResourceGenerator($this->files),
            'actions' => new ActionGenerator($this->files),
            'services' => new ServiceGenerator($this->files),
            'controller' => new ControllerGenerator($this->files),
            'routes' => new RouteGenerator($this->files),
            'views' => new ViewGenerator($this->files),
            'tests' => new TestGenerator($this->files),
        ];
    }

    private function buildContext(string $name, array $options): array
    {
        $studlyName = Str::studly($name);
        $snakeName = Str::snake($name);
        $kebabName = Str::kebab($name);
        $pluralStudly = Str::studly(Str::plural($name));
        $pluralSnake = Str::snake(Str::plural($name));
        $pluralKebab = Str::kebab(Str::plural($name));

        return [
            'name' => $name,
            'studly_name' => $studlyName,
            'snake_name' => $snakeName,
            'kebab_name' => $kebabName,
            'plural_studly' => $pluralStudly,
            'plural_snake' => $pluralSnake,
            'plural_kebab' => $pluralKebab,
            'table_name' => $pluralSnake,
            'model_class' => $studlyName,
            'controller_class' => $studlyName.'Controller',
            'policy_class' => $studlyName.'Policy',
            'request_store_class' => 'Store'.$studlyName.'Request',
            'request_update_class' => 'Update'.$studlyName.'Request',
            'resource_class' => $studlyName.'Resource',
            'factory_class' => $studlyName.'Factory',
            'seeder_class' => $studlyName.'Seeder',
            'test_feature_class' => $studlyName.'Test',
            'test_unit_class' => $studlyName.'UnitTest',
            'options' => $options,
            'relationships' => [
                'belongs_to' => $options['belongs_to'] ?? [],
                'has_many' => $options['has_many'] ?? [],
                'has_one' => $options['has_one'] ?? [],
            ],
        ];
    }

    private function shouldGenerate(string $type, array $options): bool
    {
        return match ($type) {
            'migration' => true, // Always generate migration
            'model' => true, // Always generate model
            'factory' => $options['generate_factory'] ?? config('turbomaker.defaults.generate_factory', true),
            'seeder' => $options['generate_seeder'] ?? config('turbomaker.defaults.generate_seeder', false),
            'policy' => $options['generate_policies'] ?? config('turbomaker.defaults.generate_policies', false),
            'rules' => $options['generate_rules'] ?? config('turbomaker.defaults.generate_rules', false),
            'observers' => $options['generate_observers'] ?? config('turbomaker.defaults.generate_observers', false),
            'request' => true, // Always generate for validation
            'resource' => $options['api_only'] || config('turbomaker.defaults.generate_api_resources', true),
            'actions' => $options['generate_actions'] ?? config('turbomaker.defaults.generate_actions', false),
            'services' => $options['generate_services'] ?? config('turbomaker.defaults.generate_services', false),
            'controller' => true, // Always generate controller
            'routes' => true, // Always generate routes
            'views' => $options['generate_views'] ?? true,
            'tests' => $options['generate_tests'] ?? config('turbomaker.defaults.generate_tests', true),
            default => false,
        };
    }
}
