<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class BaseGenerator
{
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    abstract public function generate(array $context): array;

    protected function getStub(string $name): string
    {
        $stubPath = __DIR__.'/../../stubs/'.$name.'.stub';

        if (! $this->files->exists($stubPath)) {
            throw new InvalidArgumentException("Stub file not found: {$stubPath}");
        }

        return $this->files->get($stubPath);
    }

    protected function replaceTokens(string $content, array $context): string
    {
        $tokens = [
            '{{ namespace }}' => $this->getNamespace($context),
            '{{ class }}' => $context['model_class'],
            '{{ controller_class }}' => $context['controller_class'],
            '{{ policy_class }}' => $context['policy_class'],
            '{{ request_store_class }}' => $context['request_store_class'],
            '{{ request_update_class }}' => $context['request_update_class'],
            '{{ resource_class }}' => $context['resource_class'],
            '{{ factory_class }}' => $context['factory_class'],
            '{{ seeder_class }}' => $context['seeder_class'],
            '{{ test_feature_class }}' => $context['test_feature_class'],
            '{{ test_unit_class }}' => $context['test_unit_class'],
            '{{ action_class }}' => $context['action_class'] ?? '',
            '{{ service_class }}' => $context['service_class'] ?? '',
            '{{ table_name }}' => $context['table_name'],
            '{{ studly_name }}' => $context['studly_name'],
            '{{ snake_name }}' => $context['snake_name'],
            '{{ kebab_name }}' => $context['kebab_name'],
            '{{ plural_studly }}' => $context['plural_studly'],
            '{{ plural_snake }}' => $context['plural_snake'],
            '{{ plural_kebab }}' => $context['plural_kebab'],
            '{{ variable }}' => Str::camel($context['studly_name']),
            '{{ variables }}' => Str::camel($context['plural_studly']),
            '{{ model_variable }}' => Str::camel($context['studly_name']),
            '{{ model_namespace }}' => 'App\\Models',
            '{{ request_namespace }}' => 'App\\Http\\Requests',
            '{{ relationships }}' => $this->generateRelationships($context),
            '{{ imports }}' => $this->generateImports($context),
            '{{ fillable }}' => $this->generateFillable($context),
            '{{ action_fillable }}' => $this->generateActionFillable($context),
            '{{ service_fillable }}' => $this->generateServiceFillable($context),
        ];

        return str_replace(array_keys($tokens), array_values($tokens), $content);
    }

    protected function ensureDirectoryExists(string $path): void
    {
        $directory = dirname($path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    protected function writeFile(string $path, string $content, bool $force = false): bool
    {
        if (! $force && $this->files->exists($path)) {
            return false; // File exists and we're not forcing
        }

        $this->ensureDirectoryExists($path);
        $this->files->put($path, $content);

        return true;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Models';
    }

    protected function generateRelationships(array $context): string
    {
        $relationships = [];

        // Belongs to relationships
        foreach ($context['relationships']['belongs_to'] as $relation) {
            $relationClass = Str::studly($relation);
            $relationVariable = Str::camel($relation);
            $foreignKey = Str::snake($relation).'_id';

            $relationships[] = "    public function {$relationVariable}(): BelongsTo";
            $relationships[] = '    {';
            $relationships[] = "        return \$this->belongsTo({$relationClass}::class);";
            $relationships[] = '    }';
            $relationships[] = '';
        }

        // Has many relationships
        foreach ($context['relationships']['has_many'] as $relation) {
            $relationClass = Str::studly(Str::singular($relation));
            $relationVariable = Str::camel(Str::plural($relation));

            $relationships[] = "    public function {$relationVariable}(): HasMany";
            $relationships[] = '    {';
            $relationships[] = "        return \$this->hasMany({$relationClass}::class);";
            $relationships[] = '    }';
            $relationships[] = '';
        }

        // Has one relationships
        foreach ($context['relationships']['has_one'] as $relation) {
            $relationClass = Str::studly($relation);
            $relationVariable = Str::camel($relation);

            $relationships[] = "    public function {$relationVariable}(): HasOne";
            $relationships[] = '    {';
            $relationships[] = "        return \$this->hasOne({$relationClass}::class);";
            $relationships[] = '    }';
            $relationships[] = '';
        }

        return implode("\n", $relationships);
    }

    protected function generateImports(array $context): string
    {
        $imports = [];

        if (! empty($context['relationships']['belongs_to'])) {
            $imports[] = 'use Illuminate\Database\Eloquent\Relations\BelongsTo;';
        }

        if (! empty($context['relationships']['has_many'])) {
            $imports[] = 'use Illuminate\Database\Eloquent\Relations\HasMany;';
        }

        if (! empty($context['relationships']['has_one'])) {
            $imports[] = 'use Illuminate\Database\Eloquent\Relations\HasOne;';
        }

        return implode("\n", $imports);
    }

    protected function generateFillable(array $context): string
    {
        $fillable = ['name']; // Default fillable field

        // Add foreign keys for belongs_to relationships
        foreach ($context['relationships']['belongs_to'] as $relation) {
            $fillable[] = Str::snake($relation).'_id';
        }

        $quoted = array_map(fn ($field): string => "'{$field}'", $fillable);

        return implode(',', $quoted);
    }

    protected function generateActionFillable(array $context): string
    {
        $fillable = [];

        // Add foreign keys for belongs_to relationships
        foreach ($context['relationships']['belongs_to'] as $relation) {
            $field = Str::snake($relation).'_id';
            $fillable[] = "            '{$field}' => \$request->{$field},";
        }

        return implode("\n", $fillable);
    }

    protected function generateServiceFillable(array $context): string
    {
        $fillable = [];

        // Add foreign keys for belongs_to relationships
        foreach ($context['relationships']['belongs_to'] as $relation) {
            $field = Str::snake($relation).'_id';
            $fillable[] = "            '{$field}' => \$request->{$field},";
        }

        return implode("\n", $fillable);
    }
}
