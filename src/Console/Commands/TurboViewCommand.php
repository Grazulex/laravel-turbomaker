<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Console\Commands;

use Exception;
use Grazulex\LaravelTurbomaker\Generators\ViewGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class TurboViewCommand extends Command
{
    protected $signature = 'turbo:view 
                            {name : The name of the module to generate views for}
                            {--relationships= : Define relationships (format: "user:belongsTo,posts:hasMany")}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate only the views for a Laravel module';

    private ViewGenerator $generator;

    public function __construct(ViewGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        $this->info("🎨 Generating views for: {$name}");

        // Check if model exists
        $modelClass = 'App\\Models\\'.Str::studly($name);
        if (! class_exists($modelClass)) {
            $this->newLine();
            $this->warn("⚠️  Model {$modelClass} does not exist.");
            $this->line("Consider running: <fg=cyan>php artisan turbo:make {$name}</> to create the complete module first.");

            if (! $this->confirm('Do you want to continue generating views anyway?')) {
                $this->error('❌ View generation cancelled.');

                return self::FAILURE;
            }
        }

        $this->newLine();

        $context = $this->buildContext($name);

        try {
            $generated = $this->generator->generate($context);

            $this->displayGeneratedFiles($generated);
            $this->displayNextSteps($name);

            $this->newLine();
            $this->info("✅ Views for '{$name}' generated successfully!");

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ Failed to generate views: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function buildContext(string $name): array
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
            'options' => [
                'generate_views' => true,
                'force' => $this->option('force'),
            ],
            'relationships' => [
                'belongs_to' => [],
                'has_many' => [],
                'has_one' => [],
            ],
        ];
    }

    private function displayGeneratedFiles(array $generated): void
    {
        $this->info('📁 Generated view files:');

        foreach ($generated as $file) {
            $this->line("    <fg=green>✓</> {$file}");
        }
    }

    private function displayNextSteps(string $name): void
    {
        $this->newLine();
        $this->info('🎯 Next steps:');

        $route = Str::kebab(Str::plural($name));
        $this->line("  1. Ensure your routes are set up: <fg=cyan>Route::resource('{$route}', {$name}Controller::class)</>");
        $this->line("  2. Make sure your controller exists: <fg=cyan>php artisan make:controller {$name}Controller</>");
        $this->line('  3. Customize the generated views as needed');
        $this->line("  4. Visit: <fg=cyan>http://your-app/{$route}</>");
    }
}
