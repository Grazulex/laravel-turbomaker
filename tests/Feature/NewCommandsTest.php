<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class NewCommandsTest extends TestCase
{
    #[Test]
    public function turbo_view_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('turbo:view')
            ->assertExitCode(0);
    }

    #[Test]
    public function turbo_api_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('turbo:api')
            ->assertExitCode(0);
    }

    #[Test]
    public function turbo_test_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('turbo:test')
            ->assertExitCode(0);
    }

    #[Test]
    public function turbo_make_command_is_still_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('turbo:make')
            ->assertExitCode(0);
    }

    #[Test]
    public function turbo_test_validates_conflicting_flags(): void
    {
        $this->artisan('turbo:test', [
            'name' => 'Product',
            '--unit' => true,
            '--feature' => true,
        ])
            ->expectsOutput('❌ Cannot use both --unit and --feature flags. Choose one or neither for both types.')
            ->assertExitCode(1);
    }

    #[Test]
    public function turbo_commands_show_help_when_called_without_arguments(): void
    {
        $this->artisan('turbo:view --help')
            ->expectsOutputToContain('Generate only the views for a Laravel module')
            ->assertExitCode(0);

        $this->artisan('turbo:api --help')
            ->expectsOutputToContain('Scaffold only API Resources & Controllers for a Laravel module')
            ->assertExitCode(0);

        $this->artisan('turbo:test --help')
            ->expectsOutputToContain('Generate Pest tests for an existing Laravel module')
            ->assertExitCode(0);
    }
}
