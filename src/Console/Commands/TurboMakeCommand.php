<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Console\Commands;

use Exception;
use Grazulex\LaravelTurbomaker\Generators\ModuleGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class TurboMakeCommand extends Command
{
    protected $signature = 'turbo:make 
                            {name : The name of the module to generate}
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

    protected $description = 'Generate a complete Laravel module with models, controllers, views, tests, and more';

    private ModuleGenerator $generator;

    public function __construct(ModuleGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $options = $this->getGenerationOptions();

        $this->info("🚀 Generating Laravel module: {$name}");
        $this->newLine();

        try {
            $generated = $this->generator->generate($name, $options);

            $this->displayGeneratedFiles($generated);
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
}
