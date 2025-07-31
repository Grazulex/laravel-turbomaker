<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker;

use Grazulex\LaravelTurbomaker\Console\Commands\TurboApiCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboMakeCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboTestCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboViewCommand;
use Illuminate\Support\ServiceProvider;

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

        // Register the main manager
        $this->app->singleton(TurbomakerManager::class, function ($app): TurbomakerManager {
            return new TurbomakerManager($app);
        });

        // Register alias
        $this->app->alias(TurbomakerManager::class, 'turbomaker');

        // Register commands in the container
        $this->app->singleton(TurboMakeCommand::class);
        $this->app->singleton(TurboViewCommand::class);
        $this->app->singleton(TurboApiCommand::class);
        $this->app->singleton(TurboTestCommand::class);
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
                TurboViewCommand::class,
                TurboApiCommand::class,
                TurboTestCommand::class,
            ]);
        }

        // Register views if needed
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'turbomaker');

        // Publish views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/turbomaker'),
            ], 'turbomaker-views');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            TurbomakerManager::class,
            'turbomaker',
        ];
    }
}
