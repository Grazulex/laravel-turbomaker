<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Console\Commands;

use Exception;
use Grazulex\LaravelTurbomaker\TurboSchemaManager;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class TurboSchemaCommand extends Command
{
    protected $signature = 'turbo:schema 
                            {action : Action to perform (list|create|show|validate|clear-cache)}
                            {name? : Schema name (required for create, show, validate)}
                            {--fields= : Field definitions for create action}
                            {--template= : Use a template (basic|ecommerce|blog)}
                            {--force : Overwrite existing schema file}';

    protected $description = 'Manage TurboMaker schemas';

    private TurboSchemaManager $schemaManager;

    public function __construct(TurboSchemaManager $schemaManager)
    {
        parent::__construct();
        $this->schemaManager = $schemaManager;
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listSchemas(),
            'create' => $this->createSchema(),
            'show' => $this->showSchema(),
            'validate' => $this->validateSchema(),
            'clear-cache' => $this->clearCache(),
            default => $this->invalidAction($action),
        };
    }

    private function listSchemas(): int
    {
        $this->info('📋 Available schemas:');
        $this->newLine();

        $schemas = $this->schemaManager->listSchemas();

        if ($schemas === []) {
            $this->line('  <fg=yellow>No schemas found</fg=yellow>');
            $this->newLine();
            $this->line('Create a new schema with: <fg=cyan>php artisan turbo:schema create MyModel</fg=cyan>');

            return Command::SUCCESS;
        }

        foreach ($schemas as $name => $schema) {
            if (! $schema) {
                continue;
            }

            $fieldsCount = count($schema->fields);
            $relationsCount = count($schema->relationships);
            $description = $schema->metadata['description'] ?? 'No description';

            $this->line("  <fg=green>✓</fg=green> <fg=cyan>{$name}</fg=cyan>");
            $this->line("    Fields: {$fieldsCount}, Relations: {$relationsCount}");
            $this->line("    Description: {$description}");
            $this->newLine();
        }

        return Command::SUCCESS;
    }

    private function createSchema(): int
    {
        $name = $this->argument('name');

        if (! $name) {
            $this->error('Schema name is required for create action');

            return Command::FAILURE;
        }

        // Check if schema already exists
        if ($this->schemaManager->schemaExists($name) && ! $this->option('force')) {
            $this->error("Schema '{$name}' already exists. Use --force to overwrite.");

            return Command::FAILURE;
        }

        $fields = $this->buildFields();
        $relationships = $this->buildRelationships();

        try {
            $filePath = $this->schemaManager->createSchemaFile($name, $fields, $relationships);

            $this->info("✅ Schema '{$name}' created successfully!");
            $this->line("📄 File: {$filePath}");
            $this->newLine();
            $this->line('Edit the schema file to customize fields and relationships.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ Failed to create schema: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function showSchema(): int
    {
        $name = $this->argument('name');

        if (! $name) {
            $this->error('Schema name is required for show action');

            return Command::FAILURE;
        }

        try {
            $schema = $this->schemaManager->getParser()->parse($name);

            if (! $schema instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
                $this->error("Schema '{$name}' not found");

                return Command::FAILURE;
            }

            $this->displaySchemaDetails($schema);

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ Failed to load schema: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function validateSchema(): int
    {
        $name = $this->argument('name');

        if (! $name) {
            $this->error('Schema name is required for validate action');

            return Command::FAILURE;
        }

        try {
            $schema = $this->schemaManager->getParser()->parse($name);

            if (! $schema instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
                $this->error("Schema '{$name}' not found");

                return Command::FAILURE;
            }

            $errors = $this->schemaManager->validateSchema($schema);

            if ($errors === []) {
                $this->info("✅ Schema '{$schema->name}' is valid!");

                return Command::SUCCESS;
            }

            $this->error('❌ Schema validation failed:');
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }

            return Command::FAILURE;
        } catch (Exception $e) {
            $this->error("❌ Failed to validate schema: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function clearCache(): int
    {
        try {
            $this->schemaManager->clearCache();
            $this->info('✅ Schema cache cleared successfully!');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ Failed to clear cache: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->line('Available actions: list, create, show, validate, clear-cache');

        return Command::FAILURE;
    }

    private function buildFields(): array
    {
        $fieldsOption = $this->option('fields');
        $template = $this->option('template');

        if ($fieldsOption) {
            return $this->parseFieldsOption($fieldsOption);
        }

        if ($template) {
            return $this->getTemplateFields($template);
        }

        // Default basic fields
        return [
            'name' => [
                'type' => 'string',
                'nullable' => false,
                'comment' => 'Name field',
            ],
        ];
    }

    private function parseFieldsOption(string $fieldsOption): array
    {
        $fields = [];
        $fieldDefinitions = explode(',', $fieldsOption);

        foreach ($fieldDefinitions as $fieldDef) {
            $parts = explode(':', mb_trim($fieldDef));

            if (count($parts) < 2) {
                continue;
            }

            $name = mb_trim($parts[0]);
            $type = mb_trim($parts[1]);
            $options = array_slice($parts, 2);

            $fieldConfig = [
                'type' => $type,
                'nullable' => false,
            ];

            foreach ($options as $option) {
                if ($option === 'nullable') {
                    $fieldConfig['nullable'] = true;
                } elseif ($option === 'unique') {
                    $fieldConfig['unique'] = true;
                } elseif ($option === 'index') {
                    $fieldConfig['index'] = true;
                }
            }

            $fields[$name] = $fieldConfig;
        }

        return $fields;
    }

    private function getTemplateFields(string $template): array
    {
        return match ($template) {
            'basic' => [
                'name' => ['type' => 'string', 'nullable' => false],
                'description' => ['type' => 'text', 'nullable' => true],
                'is_active' => ['type' => 'boolean', 'default' => true],
            ],
            'ecommerce' => [
                'name' => ['type' => 'string', 'nullable' => false],
                'slug' => ['type' => 'string', 'unique' => true],
                'description' => ['type' => 'text', 'nullable' => true],
                'price' => ['type' => 'decimal', 'length' => '8,2'],
                'stock_quantity' => ['type' => 'integer', 'default' => 0],
                'is_active' => ['type' => 'boolean', 'default' => true],
            ],
            'blog' => [
                'title' => ['type' => 'string', 'nullable' => false],
                'slug' => ['type' => 'string', 'unique' => true],
                'content' => ['type' => 'text', 'nullable' => false],
                'excerpt' => ['type' => 'text', 'nullable' => true],
                'published_at' => ['type' => 'datetime', 'nullable' => true],
                'is_featured' => ['type' => 'boolean', 'default' => false],
            ],
            default => throw new InvalidArgumentException("Unknown template: {$template}"),
        };
    }

    private function buildRelationships(): array
    {
        // For now, return empty. Users can add relationships manually
        return [];
    }

    private function displaySchemaDetails(\Grazulex\LaravelTurbomaker\Schema\Schema $schema): void
    {
        $this->info("📋 Schema: {$schema->name}");
        $this->newLine();

        // Metadata
        if ($schema->metadata !== []) {
            $this->line('<fg=cyan>Metadata:</fg=cyan>');
            foreach ($schema->metadata as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $this->line("  {$key}: {$value}");
            }
            $this->newLine();
        }

        // Fields
        if ($schema->fields !== []) {
            $this->line('<fg=cyan>Fields:</fg=cyan>');
            foreach ($schema->fields as $field) {
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
                $this->line("  <fg=green>✓</fg=green> {$field->name}: {$field->type}{$attributesStr}");
            }
            $this->newLine();
        }

        // Relationships
        if ($schema->relationships !== []) {
            $this->line('<fg=cyan>Relationships:</fg=cyan>');
            foreach ($schema->relationships as $relationship) {
                $model = class_basename($relationship->model);
                $this->line("  <fg=green>✓</fg=green> {$relationship->name}: {$relationship->type} -> {$model}");
            }
            $this->newLine();
        }

        // Options
        if ($schema->options !== []) {
            $this->line('<fg=cyan>Options:</fg=cyan>');
            foreach ($schema->options as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $this->line("  {$key}: {$value}");
            }
        }
    }
}
