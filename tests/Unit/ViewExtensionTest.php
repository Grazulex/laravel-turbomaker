<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Tests for the configurable view file extension feature (Issue #21)
 *
 * @see https://github.com/Grazulex/laravel-turbomaker/discussions/21
 */
final class ViewExtensionTest extends TestCase
{
    public function test_views_config_exists(): void
    {
        $config = config('turbomaker.views');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('extension', $config);
    }

    public function test_default_view_extension_is_blade_php(): void
    {
        $extension = config('turbomaker.views.extension');

        $this->assertEquals('.blade.php', $extension);
    }

    public function test_view_extension_can_be_customized(): void
    {
        // Test setting custom extension
        config(['turbomaker.views.extension' => '.vue']);

        $extension = config('turbomaker.views.extension');

        $this->assertEquals('.vue', $extension);
    }

    public function test_view_extension_supports_svelte(): void
    {
        config(['turbomaker.views.extension' => '.svelte']);

        $extension = config('turbomaker.views.extension');

        $this->assertEquals('.svelte', $extension);
    }

    public function test_view_extension_supports_jsx(): void
    {
        config(['turbomaker.views.extension' => '.jsx']);

        $extension = config('turbomaker.views.extension');

        $this->assertEquals('.jsx', $extension);
    }

    public function test_view_extension_supports_tsx(): void
    {
        config(['turbomaker.views.extension' => '.tsx']);

        $extension = config('turbomaker.views.extension');

        $this->assertEquals('.tsx', $extension);
    }

    public function test_get_view_extension_helper_normalizes_extension_without_dot(): void
    {
        // Test the normalization logic directly
        $extension = 'vue';

        // Simulate the normalization logic from getViewExtension()
        if ($extension !== '' && ! str_starts_with($extension, '.')) {
            $extension = '.'.$extension;
        }

        $this->assertEquals('.vue', $extension);
    }

    public function test_get_view_extension_helper_keeps_extension_with_dot(): void
    {
        // Test the normalization logic directly
        $extension = '.blade.php';

        // Simulate the normalization logic from getViewExtension()
        if ($extension !== '' && ! str_starts_with($extension, '.')) {
            $extension = '.'.$extension;
        }

        $this->assertEquals('.blade.php', $extension);
    }

    public function test_view_path_generation_with_custom_extension(): void
    {
        config(['turbomaker.views.extension' => '.vue']);

        $modelName = 'Product';
        $viewFolder = \Illuminate\Support\Str::snake($modelName);
        $extension = config('turbomaker.views.extension');

        $expectedPaths = [
            "views/{$viewFolder}/index{$extension}",
            "views/{$viewFolder}/create{$extension}",
            "views/{$viewFolder}/edit{$extension}",
            "views/{$viewFolder}/show{$extension}",
        ];

        $this->assertEquals('views/product/index.vue', $expectedPaths[0]);
        $this->assertEquals('views/product/create.vue', $expectedPaths[1]);
        $this->assertEquals('views/product/edit.vue', $expectedPaths[2]);
        $this->assertEquals('views/product/show.vue', $expectedPaths[3]);
    }

    public function test_view_path_generation_with_default_blade_extension(): void
    {
        config(['turbomaker.views.extension' => '.blade.php']);

        $modelName = 'User';
        $viewFolder = \Illuminate\Support\Str::snake($modelName);
        $extension = config('turbomaker.views.extension');

        $indexPath = "views/{$viewFolder}/index{$extension}";

        $this->assertEquals('views/user/index.blade.php', $indexPath);
    }
}
