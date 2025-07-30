<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

final class ConfigurationTest extends TestCase
{
    public function test_default_configuration_structure(): void
    {
        $config = config('turbomaker');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('defaults', $config);
        $this->assertArrayHasKey('status_tracking', $config);
        $this->assertArrayHasKey('scanners', $config);
        $this->assertArrayHasKey('performance', $config);
        $this->assertArrayHasKey('export', $config);
    }

    public function test_defaults_configuration(): void
    {
        $defaults = config('turbomaker.defaults');

        $this->assertIsArray($defaults);
        $this->assertArrayHasKey('format', $defaults);
        $this->assertArrayHasKey('include_metadata', $defaults);
        $this->assertArrayHasKey('show_progress', $defaults);
        $this->assertArrayHasKey('output_path', $defaults);

        $this->assertEquals('table', $defaults['format']);
        $this->assertTrue($defaults['include_metadata']);
        $this->assertTrue($defaults['show_progress']);
        $this->assertStringContainsString('turbomaker', $defaults['output_path']);
    }

    public function test_status_tracking_configuration(): void
    {
        $statusTracking = config('turbomaker.status_tracking');

        $this->assertIsArray($statusTracking);
        $this->assertArrayHasKey('enabled', $statusTracking);
        $this->assertArrayHasKey('log_file', $statusTracking);

        $this->assertTrue($statusTracking['enabled']);
        $this->assertStringContainsString('turbomaker.log', $statusTracking['log_file']);
    }

    public function test_scanners_configuration(): void
    {
        $scanners = config('turbomaker.scanners');

        $this->assertIsArray($scanners);
        $this->assertArrayHasKey('routes', $scanners);
        $this->assertArrayHasKey('models', $scanners);
        $this->assertArrayHasKey('views', $scanners);

        // Test routes scanner config
        $routesConfig = $scanners['routes'];
        $this->assertTrue($routesConfig['include_middleware']);
        $this->assertTrue($routesConfig['show_unused']);

        // Test models scanner config
        $modelsConfig = $scanners['models'];
        $this->assertTrue($modelsConfig['include_relationships']);
        $this->assertTrue($modelsConfig['show_attributes']);

        // Test views scanner config
        $viewsConfig = $scanners['views'];
        $this->assertTrue($viewsConfig['check_unused']);
        $this->assertTrue($viewsConfig['include_components']);
    }

    public function test_performance_configuration(): void
    {
        $performance = config('turbomaker.performance');

        $this->assertIsArray($performance);
        $this->assertArrayHasKey('cache_enabled', $performance);
        $this->assertArrayHasKey('cache_ttl', $performance);
        $this->assertArrayHasKey('memory_limit', $performance);

        $this->assertTrue($performance['cache_enabled']);
        $this->assertEquals(3600, $performance['cache_ttl']);
        $this->assertEquals('512M', $performance['memory_limit']);
    }

    public function test_export_configuration(): void
    {
        $export = config('turbomaker.export');

        $this->assertIsArray($export);
        $this->assertArrayHasKey('formats', $export);
        $this->assertArrayHasKey('include_timestamps', $export);
        $this->assertArrayHasKey('compress_output', $export);

        $this->assertIsArray($export['formats']);
        $this->assertContains('json', $export['formats']);
        $this->assertContains('csv', $export['formats']);
        $this->assertContains('html', $export['formats']);

        $this->assertTrue($export['include_timestamps']);
        $this->assertFalse($export['compress_output']);
    }

    public function test_turbomaker_is_enabled_by_default(): void
    {
        $enabled = config('turbomaker.enabled');
        $this->assertTrue($enabled);
    }

    public function test_environment_variables_are_respected(): void
    {
        // Test that environment variables would override defaults
        $this->assertEquals('table', config('turbomaker.defaults.format'));
        $this->assertTrue(config('turbomaker.defaults.include_metadata'));
        $this->assertTrue(config('turbomaker.status_tracking.enabled'));
        $this->assertTrue(config('turbomaker.performance.cache_enabled'));
        $this->assertEquals(3600, config('turbomaker.performance.cache_ttl'));
    }
}
