<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker;

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

            // Register commands
            $this->commands([
                //
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
