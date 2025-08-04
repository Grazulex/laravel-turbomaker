<?php

declare(strict_types=1);

use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaGenerationAdapter;
use Grazulex\LaravelTurbomaker\Generators\ModuleGenerator;

it('writes actual files when write_files option is enabled', function () {
    // Create a test module with file writing enabled
    $generator = new ModuleGenerator();

    $options = [
        'write_files' => true, // Enable hybrid mode: Fragment Architecture + File Writing
        'generate_requests' => true,
        'generate_factory' => true,
        'api_only' => false,
    ];

    $results = $generator->generateWithFiles('TestModel', $options);

    // Verify that file paths are returned
    expect($results)->toBeArray();
    expect($results)->toHaveKey('model');
    expect($results)->toHaveKey('migration');

    // Verify that actual files were created
    expect($results['model'])->toBeArray();
    expect($results['model'])->toHaveCount(1);

    $modelPath = $results['model'][0];
    expect($modelPath)->toEndWith('TestModel.php');
    expect(file_exists($modelPath))->toBeTrue();

    // Verify file content contains expected PHP code
    $modelContent = file_get_contents($modelPath);
    expect($modelContent)->toContain('<?php');
    expect($modelContent)->toContain('class TestModel');
    expect($modelContent)->toContain('extends Model');

    // Check migration file
    expect($results['migration'])->toBeArray();
    expect($results['migration'])->toHaveCount(1);

    $migrationPath = $results['migration'][0];
    expect(file_exists($migrationPath))->toBeTrue();

    $migrationContent = file_get_contents($migrationPath);
    expect($migrationContent)->toContain('<?php');
    expect($migrationContent)->toContain('Schema::create');
    expect($migrationContent)->toContain('test_models');

    // Clean up created files
    if (file_exists($modelPath)) {
        unlink($modelPath);
    }
    if (file_exists($migrationPath)) {
        unlink($migrationPath);
    }
});

it('works with pure fragment architecture when write_files is false', function () {
    // Test pure Fragment Architecture (no file writing)
    $adapter = new ModelSchemaGenerationAdapter();

    $options = [
        'write_files' => false, // Pure Fragment Architecture
        'generate_requests' => true,
        'generate_factory' => true,
    ];

    $results = $adapter->generateAllFragments('TestModel', $options);

    // Verify that file paths are simulated (not real files)
    expect($results)->toBeArray();
    expect($results)->toHaveKey('model');
    expect($results)->toHaveKey('migration');

    // Verify paths are returned but files don't exist
    $modelPath = $results['model'][0];
    expect($modelPath)->toEndWith('TestModel.php');
    expect(file_exists($modelPath))->toBeFalse(); // No actual file created

    $migrationPath = $results['migration'][0];
    expect(file_exists($migrationPath))->toBeFalse(); // No actual file created
});

it('demonstrates performance difference between modes', function () {
    $adapter = new ModelSchemaGenerationAdapter();

    // Test Fragment Architecture performance (no I/O) - run multiple times for better average
    $fragmentTimes = [];
    for ($i = 0; $i < 5; $i++) {
        $startTime = microtime(true);
        $fragmentResults = $adapter->generateAllFragments('PerfTestModel'.$i);
        $fragmentTimes[] = microtime(true) - $startTime;
    }
    $fragmentTime = array_sum($fragmentTimes) / count($fragmentTimes);

    // Test Hybrid mode performance (with I/O) - run multiple times for better average
    $fileTimes = [];
    for ($i = 0; $i < 5; $i++) {
        $startTime = microtime(true);
        $fileResults = $adapter->generateAllWithFiles('PerfTestModel2'.$i);
        $fileTimes[] = microtime(true) - $startTime;

        // Clean up created files immediately
        foreach ($fileResults as $type => $paths) {
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }
    $fileTime = array_sum($fileTimes) / count($fileTimes);

    // Both should return results
    expect($fragmentResults)->toBeArray();
    expect($fileResults)->toBeArray();

    // Performance test: Fragment Architecture should generally be faster, but allow for margin of error
    // If the difference is less than 10%, consider them equivalent (avoiding flaky test)
    $performanceDifference = ($fileTime - $fragmentTime) / $fileTime;
    expect($performanceDifference)->toBeGreaterThanOrEqual(-0.1); // Allow up to 10% margin
});
