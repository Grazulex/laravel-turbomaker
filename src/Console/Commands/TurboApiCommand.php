<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Console\Commands;

use Exception;
use Grazulex\LaravelTurbomaker\Generators\ModuleGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class TurboApiCommand extends Command
{
    protected $signature = 'turbo:api 
                            {name : The name of the module to generate API for}
                            {--relationships= : Define relationships (format: "user:belongsTo,posts:hasMany")}
                            {--factory : Generate factory}
                            {--seeder : Generate seeder}
                            {--tests : Generate tests}
                            {--actions : Generate action classes}
                            {--services : Generate service classes}
                            {--rules : Generate validation rules}
                            {--observers : Generate model observers}
                            {--policies : Generate policies}
                            {--belongs-to=* : Add belongs-to relationships}
                            {--has-many=* : Add has-many relationships}
                            {--has-one=* : Add has-one relationships}
                            {--force : Overwrite existing files}';

    protected $description = 'Scaffold only API Resources & Controllers for a Laravel module';

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

        $this->info("🚀 Generating API module: {$name}");
        $this->newLine();

        try {
            $generated = $this->generator->generate($name, $options);

            $this->displayGeneratedFiles($generated);
            $this->displayNextSteps($name);

            $this->newLine();
            $this->info("✅ API module '{$name}' generated successfully!");

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ Failed to generate API module: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function getGenerationOptions(): array
    {
        return [
            'api_only' => true,
            'generate_views' => false, // No views for API
            'generate_policies' => $this->option('policies'),
            'generate_factory' => $this->option('factory'),
            'generate_seeder' => $this->option('seeder'),
            'generate_tests' => $this->option('tests') || config('turbomaker.defaults.generate_tests', true),
            'generate_actions' => $this->option('actions'),
            'generate_services' => $this->option('services'),
            'generate_rules' => $this->option('rules'),
            'generate_observers' => $this->option('observers'),
            'belongs_to' => $this->option('belongs-to') ?: [],
            'has_many' => $this->option('has-many') ?: [],
            'has_one' => $this->option('has-one') ?: [],
            'force' => $this->option('force'),
        ];
    }

    private function displayGeneratedFiles(array $generated): void
    {
        $this->info('📁 Generated API files:');

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

        $this->line('  1. Run migrations: <fg=cyan>php artisan migrate</>');
        $this->line('  2. Check your API routes: <fg=cyan>php artisan route:list --path=api</>');

        $route = Str::kebab(Str::plural($name));
        $this->line("  3. Test your API: <fg=cyan>GET /api/{$route}</>");
        $this->line('  4. Configure API authentication if needed');
        $this->line('  5. Customize the generated files as needed');
    }
}
