<?php

declare(strict_types=1);

use Grazulex\LaravelTurbomaker\Exceptions\FilesAlreadyExistException;
use Grazulex\LaravelTurbomaker\Generators\ModuleGenerator;
use Illuminate\Support\Facades\File;

function overwriteGuardPaths(string $name): array
{
    return [
        app_path("Models/{$name}.php"),
        app_path("Http/Controllers/{$name}Controller.php"),
        app_path("Http/Controllers/Api/{$name}Controller.php"),
        app_path("Http/Requests/Store{$name}Request.php"),
        app_path("Http/Requests/Update{$name}Request.php"),
        app_path("Http/Resources/{$name}Resource.php"),
        database_path("factories/{$name}Factory.php"),
        base_path("tests/Feature/{$name}Test.php"),
        base_path("tests/Unit/{$name}UnitTest.php"),
    ];
}

function cleanupOverwriteGuard(string $name, string $table): void
{
    foreach (overwriteGuardPaths($name) as $path) {
        File::delete(array_merge([$path], File::glob($path.'.*.bak')));
    }

    File::delete(File::glob(database_path("migrations/*_create_{$table}_table.php")));
}

beforeEach(function (): void {
    config(['turbomaker.schemas.backup_on_generate' => false]);
    cleanupOverwriteGuard('OverwriteGuard', 'overwrite_guards');
});

afterEach(function (): void {
    cleanupOverwriteGuard('OverwriteGuard', 'overwrite_guards');
});

it('refuses to overwrite existing files without --force', function (): void {
    $this->artisan('turbo:make', ['name' => 'OverwriteGuard', '--api' => true])
        ->assertExitCode(0);

    $modelPath = app_path('Models/OverwriteGuard.php');
    File::put($modelPath, '<?php // edited by hand');
    $migrationCount = count(File::glob(database_path('migrations/*_create_overwrite_guards_table.php')));

    $this->artisan('turbo:make', ['name' => 'OverwriteGuard', '--api' => true])
        ->expectsOutputToContain('some files already exist')
        ->expectsOutputToContain('app/Models/OverwriteGuard.php')
        ->expectsOutputToContain('Use --force to overwrite them.')
        ->assertExitCode(1);

    expect(File::get($modelPath))->toBe('<?php // edited by hand')
        ->and(File::glob(database_path('migrations/*_create_overwrite_guards_table.php')))->toHaveCount($migrationCount)
        ->and(File::glob($modelPath.'.*.bak'))->toBeEmpty();
});

it('overwrites existing files with --force without backup by default', function (): void {
    $modelPath = app_path('Models/OverwriteGuard.php');
    File::ensureDirectoryExists(dirname($modelPath));
    File::put($modelPath, '<?php // edited by hand');

    $this->artisan('turbo:make', ['name' => 'OverwriteGuard', '--api' => true, '--force' => true])
        ->assertExitCode(0);

    expect(File::get($modelPath))->toContain('class OverwriteGuard')
        ->and(File::glob($modelPath.'.*.bak'))->toBeEmpty();
});

it('backs up overwritten files when backup_on_generate is enabled', function (): void {
    config(['turbomaker.schemas.backup_on_generate' => true]);

    $modelPath = app_path('Models/OverwriteGuard.php');
    File::ensureDirectoryExists(dirname($modelPath));
    File::put($modelPath, '<?php // edited by hand');

    $this->artisan('turbo:make', ['name' => 'OverwriteGuard', '--api' => true, '--force' => true])
        ->assertExitCode(0);

    $backups = File::glob($modelPath.'.*.bak');

    expect(File::get($modelPath))->toContain('class OverwriteGuard')
        ->and($backups)->toHaveCount(1)
        ->and(File::get($backups[0]))->toBe('<?php // edited by hand')
        // Files that did not exist before are not backed up
        ->and(File::glob(app_path('Http/Resources/OverwriteGuardResource.php').'.*.bak'))->toBeEmpty();
});

it('throws before writing anything when generating files without force', function (): void {
    $generator = new ModuleGenerator();
    $options = ['api_only' => true];

    $generator->generateWithFiles('OverwriteGuard', $options);

    $resourcePath = app_path('Http/Resources/OverwriteGuardResource.php');
    File::put($resourcePath, '<?php // edited by hand');
    File::delete(app_path('Models/OverwriteGuard.php'));

    try {
        $generator->generateWithFiles('OverwriteGuard', $options);
        $this->fail('FilesAlreadyExistException was not thrown');
    } catch (FilesAlreadyExistException $e) {
        expect($e->getPaths())->toContain($resourcePath)
            ->and($e->getPaths())->not->toContain(app_path('Models/OverwriteGuard.php'));
    }

    // Nothing was written: the deleted model was not recreated
    expect(File::exists(app_path('Models/OverwriteGuard.php')))->toBeFalse()
        ->and(File::get($resourcePath))->toBe('<?php // edited by hand');
});
