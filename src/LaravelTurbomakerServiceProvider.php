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
 * TurboMaker Service Provider - ModelSchema Enterprise Edition v3.0
 * Complete ModelSchema Enterprise Framework Integration - Phase 8 COMPLETE
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

        // Register ModelSchema Enterprise services (Phase 8)
        $this->registerModelSchemaEnterpriseServices();

        // Register TurboMaker core services
        $this->registerTurboMakerServices();

        // Register legacy schema services (for backward compatibility)
        $this->registerLegacySchemaServices();

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

        // Log successful boot for enterprise services
        if (config('app.debug', false)) {
            logger('TurboMaker Enterprise v3.0: Framework booted successfully with ModelSchema integration');
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
     * Register complete ModelSchema Enterprise services suite (Phase 8)
     */
    private function registerModelSchemaEnterpriseServices(): void
    {
        // ModelSchema Adapter (TurboMaker bridge) - 13 generators support
        $this->app->singleton(ModelSchemaGenerationAdapter::class, function ($app): ModelSchemaGenerationAdapter {
            return new ModelSchemaGenerationAdapter();
        });

        // Module Generator (powered by ModelSchema) - Fragment Architecture
        $this->app->singleton(ModuleGenerator::class, function ($app): ModuleGenerator {
            return new ModuleGenerator($app->make(ModelSchemaGenerationAdapter::class));
        });

        if (config('app.debug', false)) {
            logger('TurboMaker Enterprise: ModelSchema services registered - 13 generators available');
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

        if (config('app.debug', false)) {
            logger('TurboMaker: Core services registered with enterprise features');
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

        if (config('app.debug', false)) {
            logger('TurboMaker: All commands registered (Make, API, Schema)');
        }
    }
}
