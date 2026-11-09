<?php

declare(strict_types=1);

use Grazulex\LaravelTurbomaker\Generators\ModuleGenerator;
use Grazulex\LaravelTurbomaker\Schema\SchemaParser;
use Illuminate\Support\Facades\File;

function configuredPathsRoot(): string
{
    return base_path('turbomaker-paths-test');
}

function configuredPathsAbsoluteRoot(): string
{
    return sys_get_temp_dir().'/turbomaker-paths-test-'.getmypid();
}

function useConfiguredPaths(string $prefix): void
{
    config(['turbomaker.paths' => [
        'models' => "{$prefix}/Domain/Models",
        'controllers' => "{$prefix}/Web/Controllers",
        'api_controllers' => "{$prefix}/Api/Controllers",
        'requests' => "{$prefix}/Requests",
        'resources' => "{$prefix}/Resources",
        'policies' => "{$prefix}/Policies",
        'actions' => "{$prefix}/Actions",
        'services' => "{$prefix}/Services",
        'rules' => "{$prefix}/Rules",
        'observers' => "{$prefix}/Observers",
        'migrations' => "{$prefix}/migrations",
        'factories' => "{$prefix}/factories",
        'seeders' => "{$prefix}/seeders",
        'views' => "{$prefix}/views",
        'tests' => "{$prefix}/tests",
        'feature_tests' => "{$prefix}/tests/Feature",
        'unit_tests' => "{$prefix}/tests/Unit",
    ]]);
}

function defaultPathsProbeFiles(): array
{
    return [
        app_path('Models/PathsProbe.php'),
        app_path('Http/Controllers/PathsProbeController.php'),
        app_path('Http/Controllers/Api/PathsProbeController.php'),
        app_path('Http/Requests/StorePathsProbeRequest.php'),
        app_path('Http/Requests/UpdatePathsProbeRequest.php'),
        app_path('Http/Resources/PathsProbeResource.php'),
        app_path('Policies/PathsProbePolicy.php'),
        app_path('Actions/CreatePathsProbeAction.php'),
        app_path('Actions/UpdatePathsProbeAction.php'),
        app_path('Actions/DeletePathsProbeAction.php'),
        app_path('Actions/GetPathsProbeAction.php'),
        app_path('Services/PathsProbeService.php'),
        app_path('Rules/ExistsPathsProbeRule.php'),
        app_path('Rules/UniquePathsProbeRule.php'),
        app_path('Observers/PathsProbeObserver.php'),
        database_path('factories/PathsProbeFactory.php'),
        database_path('seeders/PathsProbeSeeder.php'),
        base_path('tests/Feature/PathsProbeTest.php'),
        base_path('tests/Unit/PathsProbeUnitTest.php'),
    ];
}

function cleanupConfiguredPaths(): void
{
    File::deleteDirectory(configuredPathsRoot());
    File::deleteDirectory(configuredPathsAbsoluteRoot());
    File::delete(defaultPathsProbeFiles());
    File::delete(File::glob(database_path('migrations/*_create_paths_probes_table.php')));
    File::deleteDirectory(resource_path('views/paths_probe'));
}

beforeEach(function (): void {
    cleanupConfiguredPaths();
});

afterEach(function (): void {
    cleanupConfiguredPaths();
});

it('writes every generated file in the directories set in turbomaker.paths', function (): void {
    useConfiguredPaths('turbomaker-paths-test');
    $root = configuredPathsRoot();

    $this->artisan('turbo:make', [
        'name' => 'PathsProbe',
        '--seeder' => true,
        '--policies' => true,
        '--actions' => true,
        '--services' => true,
        '--rules' => true,
        '--observers' => true,
    ])->assertExitCode(0);

    $expected = [
        "{$root}/Domain/Models/PathsProbe.php",
        "{$root}/Web/Controllers/PathsProbeController.php",
        "{$root}/Api/Controllers/PathsProbeController.php",
        "{$root}/Requests/StorePathsProbeRequest.php",
        "{$root}/Requests/UpdatePathsProbeRequest.php",
        "{$root}/Resources/PathsProbeResource.php",
        "{$root}/Policies/PathsProbePolicy.php",
        "{$root}/Actions/CreatePathsProbeAction.php",
        "{$root}/Actions/UpdatePathsProbeAction.php",
        "{$root}/Actions/DeletePathsProbeAction.php",
        "{$root}/Actions/GetPathsProbeAction.php",
        "{$root}/Services/PathsProbeService.php",
        "{$root}/Rules/ExistsPathsProbeRule.php",
        "{$root}/Rules/UniquePathsProbeRule.php",
        "{$root}/Observers/PathsProbeObserver.php",
        "{$root}/factories/PathsProbeFactory.php",
        "{$root}/seeders/PathsProbeSeeder.php",
        "{$root}/tests/Feature/PathsProbeTest.php",
        "{$root}/tests/Unit/PathsProbeUnitTest.php",
        "{$root}/views/paths_probe/index.blade.php",
        "{$root}/views/paths_probe/create.blade.php",
        "{$root}/views/paths_probe/edit.blade.php",
        "{$root}/views/paths_probe/show.blade.php",
    ];

    foreach ($expected as $path) {
        expect(File::exists($path))->toBeTrue("Missing {$path}");
    }

    expect(File::glob("{$root}/migrations/*_create_paths_probes_table.php"))->toHaveCount(1);

    // Nothing was written at the Laravel default locations
    foreach (defaultPathsProbeFiles() as $path) {
        expect(File::exists($path))->toBeFalse("Unexpected {$path}");
    }

    expect(File::glob(database_path('migrations/*_create_paths_probes_table.php')))->toBeEmpty()
        ->and(File::isDirectory(resource_path('views/paths_probe')))->toBeFalse();
});

it('uses absolute directories as they are', function (): void {
    $absolute = configuredPathsAbsoluteRoot();
    config(['turbomaker.paths.models' => $absolute.'/Models/']);

    $this->artisan('turbo:make', ['name' => 'PathsProbe', '--api' => true])
        ->assertExitCode(0);

    expect(File::exists("{$absolute}/Models/PathsProbe.php"))->toBeTrue()
        ->and(File::exists(app_path('Models/PathsProbe.php')))->toBeFalse()
        // Keys left untouched keep their default location
        ->and(File::exists(app_path('Http/Resources/PathsProbeResource.php')))->toBeTrue();
});

it('reports the configured paths when no file is written', function (): void {
    useConfiguredPaths('turbomaker-paths-test');
    $root = configuredPathsRoot();

    $generated = (new ModuleGenerator())->generate('PathsProbe', ['api_only' => true]);

    expect($generated['model'])->toBe(["{$root}/Domain/Models/PathsProbe.php"])
        ->and($generated['resources'])->toBe(["{$root}/Resources/PathsProbeResource.php"])
        ->and($generated['controllers'])->toBe(["{$root}/Api/Controllers/PathsProbeController.php"])
        ->and(File::isDirectory($root))->toBeFalse();
});

it('falls back to the Laravel default location when a path is missing or empty', function (): void {
    config(['turbomaker.paths.models' => '']);
    config(['turbomaker.paths.resources' => null]);

    $generated = (new ModuleGenerator())->generate('PathsProbe', ['api_only' => true]);

    expect($generated['model'])->toBe([app_path('Models/PathsProbe.php')])
        ->and($generated['resources'])->toBe([app_path('Http/Resources/PathsProbeResource.php')]);
});

it('reads the schema cache lifetime from turbomaker.performance.cache_ttl', function (mixed $configured, int $expected): void {
    config(['turbomaker.performance.cache_ttl' => $configured]);

    $cacheTtl = new ReflectionMethod(SchemaParser::class, 'cacheTtl');

    expect($cacheTtl->invoke(new SchemaParser()))->toBe($expected);
})->with([
    'configured value' => [120, 120],
    'numeric string from env' => ['900', 900],
    'zero falls back to one hour' => [0, 3600],
    'invalid falls back to one hour' => ['forever', 3600],
    'missing falls back to one hour' => [null, 3600],
]);
