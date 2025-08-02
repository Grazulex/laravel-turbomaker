<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Console\Commands;

use Exception;
use Grazulex\LaravelTurbomaker\Generators\ModuleGenerator;
use Grazulex\LaravelTurbomaker\TurboSchemaManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class TurboMakeCommand extends Command
{
    protected $signature = 'turbo:make 
                            {name : The name of the module to generate}
                            {--schema= : Schema file path, name, or inline definition}
                            {--fields= : Quick field definition (e.g., "name:string,email:email:unique")}
                            {--api : Generate API resources only}
                            {--views : Generate views}
                            {--policies : Generate policies}
                            {--factory : Generate factory}
                            {--seeder : Generate seeder}
                            {--tests : Generate tests}
                            {--actions : Generate action classes}
                            {--services : Generate service classes}
                            {--rules : Generate validation rules}
                            {--observers : Generate model observers}
                            {--belongs-to=* : Add belongs-to relationships}
                            {--has-many=* : Add has-many relationships}
                            {--has-one=* : Add has-one relationships}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate a complete Laravel module with models, controllers, views, tests, and more. Supports schema-based generation.';

    private ModuleGenerator $generator;

    private TurboSchemaManager $schemaManager;

    private bool $schemaError = false;

    public function __construct(ModuleGenerator $generator, TurboSchemaManager $schemaManager)
    {
        parent::__construct();
        $this->generator = $generator;
        $this->schemaManager = $schemaManager;
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        // Resolve schema first
        $schema = $this->resolveSchema($name);

        // If there was a schema error, exit with failure
        if ($this->schemaError) {
            return Command::FAILURE;
        }

        $options = $this->getGenerationOptions();

        $this->info("🚀 Generating Laravel module: {$name}");

        if ($schema instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            $this->line("📋 Using schema: {$schema->name}");
            $fieldsCount = count($schema->fields);
            $relationsCount = count($schema->relationships);
            $this->line("Fields: {$fieldsCount}, Relations: {$relationsCount}");
        }

        $this->newLine();

        try {
            // Pass schema to generator
            $generated = $this->generator->generate($name, $options, $schema);

            $this->displayGeneratedFiles($generated);
            $this->displaySchemaInfo($schema);
            $this->displayNextSteps($name);

            $this->newLine();
            $this->info("✅ Module '{$name}' generated successfully!");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ Failed to generate module: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function getGenerationOptions(): array
    {
        return [
            'api_only' => $this->option('api'),
            'generate_views' => $this->option('views') || ! $this->option('api'),
            'generate_policies' => $this->option('policies') || config('turbomaker.defaults.generate_policies', false),
            'generate_factory' => $this->option('factory') || config('turbomaker.defaults.generate_factory', true),
            'generate_seeder' => $this->option('seeder') || config('turbomaker.defaults.generate_seeder', false),
            'generate_tests' => $this->option('tests') || config('turbomaker.defaults.generate_tests', true),
            'generate_actions' => $this->option('actions') || config('turbomaker.defaults.generate_actions', false),
            'generate_services' => $this->option('services') || config('turbomaker.defaults.generate_services', false),
            'generate_rules' => $this->option('rules') || config('turbomaker.defaults.generate_rules', false),
            'generate_observers' => $this->option('observers') || config('turbomaker.defaults.generate_observers', false),
            'belongs_to' => $this->option('belongs-to') ?: [],
            'has_many' => $this->option('has-many') ?: [],
            'has_one' => $this->option('has-one') ?: [],
            'force' => $this->option('force'),
        ];
    }

    private function displayGeneratedFiles(array $generated): void
    {
        $this->info('📁 Generated files:');

        foreach ($generated as $type => $files) {
            if (empty($files)) {
                continue;
            }

            $this->line("  <fg=cyan>{$type}:</>");
            foreach ($files as $file) {
                $this->line("    <fg=green>✓</> {$file}");
            }
        }
    }

    private function displayNextSteps(string $name): void
    {
        $this->newLine();
        $this->info('🎯 Next steps:');

        $instructions = new \Grazulex\LaravelTurbomaker\Support\PostGenerationInstructions();

        // Always need to run migrations
        $instructions->addMigrationReminder();

        // Check what was generated and add relevant instructions
        if ($this->option('observers')) {
            $instructions->addObserverRegistration($name.'Observer', $name);
        }

        if ($this->option('policies')) {
            $instructions->addPolicyRegistration($name.'Policy', $name);
        }

        if ($this->option('seeder')) {
            $instructions->addSeederReminder($name.'Seeder');
        }

        // Add route registration
        $route = Str::kebab(Str::plural($name));
        $instructions->addInstruction(
            'route_registration',
            'Add resource routes to your routes/web.php or routes/api.php:',
            "Route::resource('{$route}', \\App\\Http\\Controllers\\{$name}Controller::class);"
        );

        $stepNumber = 1;
        foreach ($instructions->getInstructions() as $instruction) {
            $this->line("  {$stepNumber}. {$instruction['message']}");
            if ($instruction['command']) {
                $this->line("     <fg=cyan>{$instruction['command']}</>");
            }
            $stepNumber++;
        }

        // Standard steps
        $this->line("  {$stepNumber}. Check your routes: <fg=cyan>php artisan route:list</>");

        if (! $this->option('api')) {
            $route = Str::kebab(Str::plural($name));
            $this->line('  '.($stepNumber + 1).". Visit: <fg=cyan>http://your-app/{$route}</>");
        }

        $this->line('  '.($stepNumber + 2).'. Customize the generated files as needed');

        // Special warnings for important registrations
        if ($this->option('observers')) {
            $this->newLine();
            $this->warn('⚠️  IMPORTANT: Don\'t forget to register your Observer in AppServiceProvider!');
        }

        if ($this->option('policies')) {
            $this->newLine();
            $this->warn('⚠️  IMPORTANT: Don\'t forget to register your Policy in AuthServiceProvider!');
        }
    }

    /**
     * Resolve schema from options
     */
    private function resolveSchema(string $modelName): ?\Grazulex\LaravelTurbomaker\Schema\Schema
    {
        $schemaOption = $this->option('schema');
        $fieldsOption = $this->option('fields');

        // Determine which input to use (fields takes precedence over schema)
        $schemaInput = $fieldsOption ?: $schemaOption;

        try {
            $schema = $this->schemaManager->resolveSchema($schemaInput, $modelName);

            if ($schema instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
                // Validate schema
                $errors = $this->schemaManager->validateSchema($schema);
                if ($errors !== []) {
                    $this->error('Schema validation failed:');
                    foreach ($errors as $error) {
                        $this->line("  - {$error}");
                    }
                    throw new Exception('Invalid schema configuration');
                }

                $this->line('✅ Schema resolved successfully');

                return $schema;
            }

            if ($schemaInput) {
                $this->warn("⚠️  Schema '{$schemaInput}' not found, using default generation");
            }

            return null;
        } catch (Exception $e) {
            $this->error("❌ Schema error: {$e->getMessage()}");
            // Signal error by setting a property that handle() can check
            $this->schemaError = true;

            return null;
        }
    }

    /**
     * Display schema information
     */
    private function displaySchemaInfo(?\Grazulex\LaravelTurbomaker\Schema\Schema $schema): void
    {
        if (! $schema instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return;
        }

        $this->newLine();
        $this->info('📋 Schema details:');

        // Display fields
        if ($schema->fields !== []) {
            $this->line('  <fg=cyan>Fields:</fg=cyan>');
            foreach ($schema->fields as $field) {
                $type = $field->type;
                $attributes = [];

                if ($field->nullable) {
                    $attributes[] = 'nullable';
                }
                if ($field->unique) {
                    $attributes[] = 'unique';
                }
                if ($field->index) {
                    $attributes[] = 'index';
                }
                if ($field->default !== null) {
                    $attributes[] = "default:{$field->default}";
                }

                $attributesStr = $attributes === [] ? '' : ' ('.implode(', ', $attributes).')';
                $this->line("    <fg=green>✓</fg=green> {$field->name}: {$type}{$attributesStr}");
            }
        }

        // Display relationships
        if ($schema->relationships !== []) {
            $this->line('  <fg=cyan>Relationships:</fg=cyan>');
            foreach ($schema->relationships as $relationship) {
                $model = class_basename($relationship->model);
                $this->line("    <fg=green>✓</fg=green> {$relationship->name}: {$relationship->type} -> {$model}");
            }
        }

        // Display options
        if ($schema->options !== []) {
            $this->line('  <fg=cyan>Options:</fg=cyan>');
            foreach ($schema->options as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $this->line("    <fg=green>✓</fg=green> {$key}: {$value}");
            }
        }
    }
}
