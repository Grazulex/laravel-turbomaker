<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Support;

use Illuminate\Support\Facades\File;

final class Laravel11Helper
{
    public static function isLaravel11OrHigher(): bool
    {
        return version_compare(app()->version(), '11.0', '>=');
    }

    public static function ensureApiRoutesEnabled(): bool
    {
        if (! self::isLaravel11OrHigher()) {
            return true; // Not Laravel 11, assume API routes are available
        }

        // Check if API routes are already enabled
        if (File::exists(base_path('routes/api.php'))) {
            return true; // API routes file exists
        }

        return false; // API routes not enabled
    }

    public static function enableApiRoutes(): void
    {
        if (! self::isLaravel11OrHigher()) {
            return; // Not Laravel 11, no action needed
        }

        // Install Laravel Sanctum (required for API routes in Laravel 11)
        if (! File::exists(base_path('vendor/laravel/sanctum'))) {
            shell_exec('composer require laravel/sanctum');
        }

        // Publish API routes
        shell_exec('php artisan install:api');
    }

    public static function getApiRouteInstructions(): array
    {
        $instructions = [];

        if (self::isLaravel11OrHigher() && ! self::ensureApiRoutesEnabled()) {
            $instructions[] = [
                'type' => 'laravel11_api',
                'message' => '🔧 Laravel 11 detected: API routes need to be enabled',
                'command' => 'php artisan install:api',
                'description' => 'This will install Laravel Sanctum and create the routes/api.php file',
            ];
        }

        return $instructions;
    }

    public static function getApiMiddlewareForLaravel11(): array
    {
        if (self::isLaravel11OrHigher()) {
            return ['api', 'auth:sanctum'];
        }

        return ['api'];
    }
}
