<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Console\Commands;

use Exception;
use Grazulex\LaravelTurbomaker\Generators\TestGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class TurboTestCommand extends Command
{
    protected $signature = 'turbo:test 
                            {name : The name of the module to generate tests for}
                            {--relationships= : Define relationships (format: "user:belongsTo,posts:hasMany")}
                            {--unit : Generate only unit tests}
                            {--feature : Generate only feature tests}
                            {--belongs-to=* : Add belongs-to relationships for test context}
                            {--has-many=* : Add has-many relationships for test context}
                            {--has-one=* : Add has-one relationships for test context}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate Pest tests for an existing Laravel module';

    private TestGenerator $generator;

    public function __construct(TestGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        // Validate flags
        if ($this->option('unit') && $this->option('feature')) {
            $this->error('❌ Cannot use both --unit and --feature flags. Choose one or neither for both types.');
            return Command::FAILURE;
        }

        $this->info("🚀 TurboMaker: Generating tests for {$name}...");
        
        if ($this->option('unit')) {
            $this->line('📋 Generating unit tests only');
        } elseif ($this->option('feature')) {
            $this->line('🧪 Generating feature tests only');
        }
        
        $this->newLine();

        $context = $this->buildContext($name);

        try {
            $generated = $this->generator->generate($context);

            $this->displayGeneratedFiles($generated);
            $this->displayNextSteps($name);

            $this->newLine();
            $this->info("✅ Tests for {$name} generated successfully!");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error generating tests: {$e->getMessage()}");
            return Command::FAILURE;
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
            'table_name' => $pluralSnake,
            'model_class' => $studlyName,
            'controller_class' => $studlyName.'Controller',
            'test_feature_class' => $studlyName.'Test',
            'test_unit_class' => $studlyName.'UnitTest',
            'options' => [
                'generate_tests' => true,
                'generate_unit_only' => $this->option('unit'),
                'generate_feature_only' => $this->option('feature'),
                'force' => $this->option('force'),
            ],
            'relationships' => [
                'belongs_to' => $this->option('belongs-to') ?: [],
                'has_many' => $this->option('has-many') ?: [],
                'has_one' => $this->option('has-one') ?: [],
            ],
        ];
    }

    private function displayGeneratedFiles(array $generated): void
    {
        $this->info('📁 Generated test files:');

        foreach ($generated as $file) {
            $this->line("    <fg=green>✓</> {$file}");
        }
    }

    private function displayNextSteps(string $name): void
    {
        $this->newLine();
        $this->info('🎯 Next steps:');

        $this->line('  1. Run your tests: <fg=cyan>php artisan test</>');
        $this->line('  2. Or run with Pest: <fg=cyan>./vendor/bin/pest</>');
        $this->line("  3. Run specific tests: <fg=cyan>./vendor/bin/pest --filter={$name}</>");
        $this->line('  4. Customize the generated tests as needed');
        $this->line('  5. Set up test database if not already configured');
    }
}
