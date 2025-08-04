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
                            {--optimize : Enable ModelSchema optimization (lazy loading, streaming)}
                            {--fragments : Use fragment-based generation}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate a complete Laravel module with ModelSchema enterprise features. Supports schema-based generation with optimization.';

    private ModuleGenerator $generator;

    private bool $schemaError = false;

    public function __construct(ModuleGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
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

            // Display schema details if requested via fields
            if ($this->option('fields')) {
                $this->displaySchemaDetails($schema);
            }
        }

        $this->newLine();

        try {
            // Use generateWithFiles to ensure actual file creation for CLI usage
            // This enables the hybrid approach: Fragment Architecture + File Writing
            $generated = $this->generator->generateWithFiles($name, $options, $schema);

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
                $relativePath = str_replace(base_path().'/', '', $file);
                $this->line("    <fg=green>✓</> {$relativePath}");
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

    private function resolveSchema(string $modelName): ?\Grazulex\LaravelTurbomaker\Schema\Schema
    {
        $schemaOption = $this->option('schema');
        $fieldsOption = $this->option('fields');

        try {
            // Check if fields shorthand provided
            if ($fieldsOption) {
                $this->line('✅ Schema resolved successfully');
                $fields = $this->parseFields($fieldsOption);
                $config = [
                    'fields' => $fields,
                    'relationships' => [],
                    'options' => [
                        'table' => mb_strtolower(Str::snake(Str::pluralStudly($modelName))),
                        'timestamps' => true,
                        'soft_deletes' => false,
                    ],
                ];

                return \Grazulex\LaravelTurbomaker\Schema\Schema::fromArray($modelName, $config);
            }

            // Check if schema file provided
            if ($schemaOption) {
                // First try as inline YAML
                if (str_contains($schemaOption, '{') || str_contains($schemaOption, 'fields:')) {
                    try {
                        $yamlData = \Symfony\Component\Yaml\Yaml::parse($schemaOption);
                        $schema = \Grazulex\LaravelTurbomaker\Schema\Schema::fromArray($modelName, $yamlData);
                        $this->line('✅ Schema resolved successfully');

                        return $schema;
                    } catch (Exception $e) {
                        // Fall through to file lookup
                    }
                }

                // Try to find schema file in resources/schemas/
                $possiblePaths = [
                    resource_path("schemas/{$schemaOption}.schema.yml"),
                    resource_path('schemas/'.Str::snake($schemaOption).'.schema.yml'),
                    resource_path("schemas/{$modelName}.schema.yml"),
                    resource_path('schemas/'.Str::snake($modelName).'.schema.yml'),
                ];

                foreach ($possiblePaths as $schemaPath) {
                    if (file_exists($schemaPath)) {
                        $yamlContent = file_get_contents($schemaPath);
                        $yamlData = \Symfony\Component\Yaml\Yaml::parse($yamlContent);

                        $schema = \Grazulex\LaravelTurbomaker\Schema\Schema::fromArray($modelName, $yamlData);
                        $this->line('✅ Schema resolved successfully');
                        $this->line('📄 Using schema: '.basename($schemaPath));

                        return $schema;
                    }
                }

                $this->warn("⚠️  Schema '{$schemaOption}' not found in any of these locations:");
                foreach ($possiblePaths as $path) {
                    $this->line('   - '.$path);
                }
                $this->warn('Using default generation instead.');
            }

            return null;
        } catch (Exception $e) {
            $this->error("❌ Schema error: {$e->getMessage()}");
            $this->schemaError = true;

            return null;
        }
    }

    private function displaySchemaDetails(\Grazulex\LaravelTurbomaker\Schema\Schema $schema): void
    {
        $this->newLine();
        $this->line('📋 Schema details:');
        $this->line('Fields:');

        foreach ($schema->fields as $field) {
            $modifiers = [];
            if ($field->nullable) {
                $modifiers[] = 'nullable';
            }
            if ($field->unique) {
                $modifiers[] = 'unique';
            }
            if ($field->index) {
                $modifiers[] = 'index';
            }

            $modifierStr = $modifiers === [] ? '' : ' ('.implode(', ', $modifiers).')';
            $this->line("  {$field->name}: {$field->type}{$modifierStr}");
        }

        if ($schema->relationships !== []) {
            $this->line('Relationships:');
            foreach ($schema->relationships as $relationship) {
                $this->line("  {$relationship->name}: {$relationship->type}");
            }
        }
    }

    private function parseFields(string $fieldsInput): array
    {
        $fields = [];
        $pairs = explode(',', $fieldsInput);

        foreach ($pairs as $pair) {
            $parts = explode(':', mb_trim($pair));
            if (count($parts) < 2) {
                continue;
            }

            $name = mb_trim($parts[0]);
            $type = mb_trim($parts[1]);
            $modifiers = array_slice($parts, 2);

            // Validate field type using ModelSchema registry
            $fieldRegistry = \Grazulex\LaravelModelschema\Support\FieldTypeRegistry::class;
            if (! $fieldRegistry::has($type)) {
                throw new Exception("Invalid field type '{$type}' for field '{$name}'");
            }

            $field = [
                'type' => $type,
                'nullable' => in_array('nullable', $modifiers),
                'unique' => in_array('unique', $modifiers),
                'index' => in_array('index', $modifiers),
            ];

            // Handle length for strings
            if (in_array($type, ['string', 'char']) && $modifiers !== []) {
                foreach ($modifiers as $modifier) {
                    if (is_numeric($modifier)) {
                        $field['length'] = (int) $modifier;
                        break;
                    }
                }
            }

            $fields[$name] = $field;
        }

        return $fields;
    }
}
