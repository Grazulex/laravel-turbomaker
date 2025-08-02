<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

use Illuminate\Support\Str;

final class MigrationGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        $timestamp = date('Y_m_d_His');
        $migrationName = "create_{$context['table_name']}_table";
        $migrationPath = database_path("migrations/{$timestamp}_{$migrationName}.php");

        $stub = $this->getStub('migration');
        $content = $this->replaceTokens($stub, $context);

        $generated = [];

        if ($this->writeFile($migrationPath, $content, $context['options']['force'] ?? false)) {
            $generated[] = $migrationPath;
        }

        return $generated;
    }

    protected function replaceTokens(string $content, array $context): string
    {
        $content = parent::replaceTokens($content, $context);

        // Add migration-specific tokens
        $migrationTokens = [
            '{{ schema_migration_fields }}' => $this->generateMigrationFields($context),
        ];

        return str_replace(array_keys($migrationTokens), array_values($migrationTokens), $content);
    }

    private function generateMigrationFields(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateMigrationFieldsString();
        }

        // Fallback to basic migration
        $fields = [];
        $fields[] = '            $table->id();';
        $fields[] = "            \$table->string('name');";

        // Add foreign keys for belongs_to relationships
        foreach ($context['relationships']['belongs_to'] as $relation) {
            $foreignKey = Str::snake($relation).'_id';
            $fields[] = "            \$table->foreignId('{$foreignKey}')->constrained();";
        }

        $fields[] = '            $table->timestamps();';

        return implode("\n", $fields);
    }
}
