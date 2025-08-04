<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker;

use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaGenerationAdapter;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboApiCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboMakeCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboSchemaCommand;
use Grazulex\LaravelTurbomaker\Generators\ModuleGenerator;
use Grazulex\LaravelTurbomaker\Schema\SchemaParser;
use Illuminate\Support\ServiceProvider;

/**
 * TurboMaker Service Provider - ModelSchema Enterprise Edition
 * Pure ModelSchema Enterprise architecture with Fragment Architecture
 */
final class LaravelTurbomakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/turbomaker.php',
            'turbomaker'
        );

        // Register ModelSchema Enterprise services
        $this->registerModelSchemaServices();

        // Register legacy schema services (for backward compatibility)
        $this->registerLegacySchemaServices();

        // Register TurboMaker core services
        $this->registerTurboMakerServices();

        // Register commands in the container
        $this->registerCommands();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/turbomaker.php' => config_path('turbomaker.php'),
            ], 'turbomaker-config');

            // Publish stubs
            $this->publishes([
                __DIR__.'/../stubs' => resource_path('stubs/turbomaker'),
            ], 'turbomaker-stubs');

            // Register commands
            $this->commands([
                TurboMakeCommand::class,
                TurboApiCommand::class,
                TurboSchemaCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            TurbomakerManager::class,
            'turbomaker',
            ModelSchemaGenerationAdapter::class,
            ModuleGenerator::class,
        ];
    }

    /**
     * Register ModelSchema Enterprise services
     */
    private function registerModelSchemaServices(): void
    {
        // Register the core ModelSchema adapter
        $this->app->singleton(ModelSchemaGenerationAdapter::class);

        // Register the main module generator (ModelSchema powered)
        $this->app->singleton(ModuleGenerator::class, function ($app): ModuleGenerator {
            return new ModuleGenerator($app->make(ModelSchemaGenerationAdapter::class));
        });

        // Debug log for ModelSchema services
        if (config('app.debug', false)) {
            logger('TurboMaker: ModelSchema Enterprise services registered');
        }
    }

    /**
     * Register legacy schema services for backward compatibility
     */
    private function registerLegacySchemaServices(): void
    {
        // Keep SchemaParser for backward compatibility with YAML schemas
        $this->app->singleton(SchemaParser::class);
        $this->app->singleton(TurboSchemaManager::class);

        // Debug log for legacy services
        if (config('app.debug', false)) {
            logger('TurboMaker: Legacy schema services registered for backward compatibility');
        }
    }

    /**
     * Register TurboMaker core services
     */
    private function registerTurboMakerServices(): void
    {
        // Register the main manager
        $this->app->singleton(TurbomakerManager::class, function ($app): TurbomakerManager {
            return new TurbomakerManager($app);
        });

        // Register alias
        $this->app->alias(TurbomakerManager::class, 'turbomaker');

        // Debug log for core services
        if (config('app.debug', false)) {
            logger('TurboMaker: Core services registered');
        }
    }

    /**
     * Register commands in the container
     */
    private function registerCommands(): void
    {
        $this->app->singleton(TurboMakeCommand::class);
        $this->app->singleton(TurboApiCommand::class);
        $this->app->singleton(TurboSchemaCommand::class);

        // Debug log for commands
        if (config('app.debug', false)) {
            logger('TurboMaker: Commands registered');
        }
    }
}
