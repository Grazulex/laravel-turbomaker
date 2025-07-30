<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Support;

final class PostGenerationInstructions
{
    private array $instructions = [];

    public function addInstruction(string $type, string $message, ?string $command = null): void
    {
        $this->instructions[] = [
            'type' => $type,
            'message' => $message,
            'command' => $command,
        ];
    }

    public function addObserverRegistration(string $observerClass, string $modelClass): void
    {
        $this->addInstruction(
            'provider_registration',
            "Register the {$observerClass} in your AppServiceProvider boot() method:",
            "\\App\\Models\\{$modelClass}::observe(\\App\\Observers\\{$observerClass}::class);"
        );
    }

    public function addPolicyRegistration(string $policyClass, string $modelClass): void
    {
        $this->addInstruction(
            'provider_registration',
            "Register the {$policyClass} in your AuthServiceProvider \$policies array:",
            "\\App\\Models\\{$modelClass}::class => \\App\\Policies\\{$policyClass}::class,"
        );
    }

    public function addRouteRegistration(string $routeName): void
    {
        $this->addInstruction(
            'route_registration',
            'Add resource routes to your routes/web.php or routes/api.php:',
            "Route::resource('{$routeName}', \\App\\Http\\Controllers\\{$routeName}Controller::class);"
        );
    }

    public function addMigrationReminder(): void
    {
        $this->addInstruction(
            'migration',
            'Run the database migrations:',
            'php artisan migrate'
        );
    }

    public function addSeederReminder(string $seederClass): void
    {
        $this->addInstruction(
            'seeder',
            "Add {$seederClass} to your DatabaseSeeder:",
            "\$this->call({$seederClass}::class);"
        );
    }

    public function addFactoryTest(): void
    {
        $this->addInstruction(
            'testing',
            'Test your factory in Tinker:',
            'php artisan tinker'
        );
    }

    public function getInstructions(): array
    {
        return $this->instructions;
    }

    public function hasInstructions(): bool
    {
        return $this->instructions !== [];
    }

    public function clear(): void
    {
        $this->instructions = [];
    }
}
