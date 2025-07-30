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

        $this->info("🚀 TurboMaker: Generating API module for {$name}...");
        $this->newLine();

        try {
            $generated = $this->generator->generate($name, $options);

            $this->displayGeneratedFiles($generated);
            $this->displayNextSteps($name);

            $this->newLine();
            $this->info("🎯 API module for {$name} generated successfully!");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ Error generating API module: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function getGenerationOptions(): array
    {
        return [
            'api_only' => true,
            'generate_api' => true,
            'generate_controllers' => true,
            'generate_models' => true,
            'generate_migrations' => true,
            'generate_requests' => true,
            'generate_resources' => true,
            'generate_routes' => true,
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

        $instructions = new \Grazulex\LaravelTurbomaker\Support\PostGenerationInstructions();
        
        // Check Laravel 11 API routes requirements
        $laravel11Instructions = \Grazulex\LaravelTurbomaker\Support\Laravel11Helper::getApiRouteInstructions();
        foreach ($laravel11Instructions as $instruction) {
            $instructions->addInstruction(
                $instruction['type'],
                $instruction['message'],
                $instruction['command']
            );
        }

        // Always need to run migrations for API
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

        // Add API route registration
        $route = Str::kebab(Str::plural($name));
        $instructions->addInstruction(
            'route_registration',
            'Add API routes to your routes/api.php:',
            "Route::apiResource('{$route}', \\App\\Http\\Controllers\\{$name}Controller::class);"
        );

        $stepNumber = 1;
        foreach ($instructions->getInstructions() as $instruction) {
            $this->line("  {$stepNumber}. {$instruction['message']}");
            if ($instruction['command']) {
                $this->line("     <fg=cyan>{$instruction['command']}</>");
            }
            $stepNumber++;
        }

        // API-specific steps
        $this->line("  {$stepNumber}. Check your API routes: <fg=cyan>php artisan route:list --path=api</>");
        $this->line('  '.($stepNumber + 1).". Test your API: <fg=cyan>GET /api/{$route}</>");
        $this->line('  '.($stepNumber + 2).'. Configure API authentication if needed');
        $this->line('  '.($stepNumber + 3).'. Customize the generated files as needed');

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
