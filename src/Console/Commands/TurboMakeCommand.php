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
            'generate_policies' => $this->option('policies'),
            'generate_factory' => $this->option('factory'),
            'generate_seeder' => $this->option('seeder'),
            'generate_tests' => $this->option('tests') || config('turbomaker.defaults.generate_tests', true),
            'generate_actions' => $this->option('actions'),
            'generate_services' => $this->option('services'),
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

        Str::studly($name);
        Str::snake(Str::plural($name));

        $this->line('  1. Run migrations: <fg=cyan>php artisan migrate</>');
        $this->line('  2. Check your routes: <fg=cyan>php artisan route:list</>');

        if (! $this->option('api')) {
            $route = Str::kebab(Str::plural($name));
            $this->line("  3. Visit: <fg=cyan>http://your-app/{$route}</>");
        }

        $this->line('  4. Customize the generated files as needed');
    }
}
