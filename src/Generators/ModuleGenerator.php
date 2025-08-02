<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\Schema\SchemaParser;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final class ModuleGenerator
{
    private Filesystem $files;

    private SchemaParser $schemaParser;

    private array $generators;

    public function __construct(Filesystem $files, ?SchemaParser $schemaParser = null)
    {
        $this->files = $files;
        $this->schemaParser = $schemaParser ?? new SchemaParser();
        $this->initializeGenerators();
    }

    public function generate(string $name, array $options = [], ?Schema $schema = null): array
    {
        $generatedFiles = [];
        $context = $this->buildContext($name, $options, $schema);

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
            'migration' => new MigrationGenerator($this->files, $this->schemaParser),
            'model' => new ModelGenerator($this->files, $this->schemaParser),
            'factory' => new FactoryGenerator($this->files, $this->schemaParser),
            'seeder' => new SeederGenerator($this->files, $this->schemaParser),
            'policy' => new PolicyGenerator($this->files, $this->schemaParser),
            'rules' => new RuleGenerator($this->files, $this->schemaParser),
            'observers' => new ObserverGenerator($this->files, $this->schemaParser),
            'request' => new RequestGenerator($this->files, $this->schemaParser),
            'resource' => new ResourceGenerator($this->files, $this->schemaParser),
            'actions' => new ActionGenerator($this->files, $this->schemaParser),
            'services' => new ServiceGenerator($this->files, $this->schemaParser),
            'controller' => new ControllerGenerator($this->files, $this->schemaParser),
            'routes' => new RouteGenerator($this->files, $this->schemaParser),
            'views' => new ViewGenerator($this->files, $this->schemaParser),
            'tests' => new TestGenerator($this->files, $this->schemaParser),
        ];
    }

    private function buildContext(string $name, array $options, ?Schema $schema = null): array
    {
        $studlyName = Str::studly($name);
        $snakeName = Str::snake($name);
        $kebabName = Str::kebab($name);
        $pluralStudly = Str::studly(Str::plural($name));
        $pluralSnake = Str::snake(Str::plural($name));
        $pluralKebab = Str::kebab(Str::plural($name));

        $context = [
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

        // Add schema to context if provided
        if ($schema instanceof Schema) {
            $context['schema'] = $schema;
        }

        return $context;
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
