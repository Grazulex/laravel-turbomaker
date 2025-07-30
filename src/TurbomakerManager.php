<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class TurbomakerManager
{
    private Application $app;

    private array $scanners = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the application instance.
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Check if Turbomaker is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) config('turbomaker.enabled', true);
    }

    /**
     * Get default configuration.
     */
    public function getDefaults(): array
    {
        return config('turbomaker.defaults', []);
    }

    /**
     * Register a scanner.
     */
    public function registerScanner(string $name, callable $scanner): void
    {
        $this->scanners[$name] = $scanner;
    }

    /**
     * Get all registered scanners.
     */
    public function getScanners(): array
    {
        return $this->scanners;
    }

    /**
     * Run a specific scanner.
     */
    public function runScanner(string $name, array $options = []): Collection
    {
        if (! isset($this->scanners[$name])) {
            throw new InvalidArgumentException("Scanner '{$name}' not found.");
        }

        $scanner = $this->scanners[$name];

        return collect($scanner($options));
    }

    /**
     * Get status tracking configuration.
     */
    public function getStatusTracking(): array
    {
        return config('turbomaker.status_tracking', []);
    }

    /**
     * Check if status tracking is enabled.
     */
    public function isStatusTrackingEnabled(): bool
    {
        return (bool) config('turbomaker.status_tracking.enabled', true);
    }

    /**
     * Get scanner configuration.
     */
    public function getScannerConfig(string $scanner): array
    {
        return config("turbomaker.scanners.{$scanner}", []);
    }

    /**
     * Get performance settings.
     */
    public function getPerformanceSettings(): array
    {
        return config('turbomaker.performance', []);
    }

    /**
     * Get export configuration.
     */
    public function getExportConfig(): array
    {
        return config('turbomaker.export', []);
    }
}
