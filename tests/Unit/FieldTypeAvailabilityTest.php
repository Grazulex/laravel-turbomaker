<?php

declare(strict_types=1);

use Grazulex\LaravelTurbomaker\Schema\FieldTypes\FieldTypeRegistry;

it('shows all available field types in test environment', function () {
    $availableTypes = FieldTypeRegistry::getAvailableTypes();

    // Debug output
    // echo "\n=== Available Field Types ===\n";
    foreach ($availableTypes as $type) {
        // echo "✓ {$type}\n";
    }
    // echo 'Total: '.count($availableTypes)." types\n";

    // Test specific types mentioned in the bug report
    $expectedTypes = [
        'string', 'text', 'longText', 'mediumText',
        'integer', 'bigInteger', 'unsignedBigInteger',
        'tinyInteger', 'smallInteger', 'mediumInteger',
        'boolean', 'decimal', 'float', 'double',
        'date', 'datetime', 'timestamp', 'time',
        'json', 'uuid', 'email', 'url',
        'foreignId', 'morphs', 'binary',
    ];

    // echo "\n=== Type Validation ===\n";
    foreach ($expectedTypes as $type) {
        $available = FieldTypeRegistry::has($type);
        // echo ($available ? '✅' : '❌')." {$type}\n";
        expect($available)->toBeTrue("Type '{$type}' should be available");
    }

    expect(count($availableTypes))->toBeGreaterThanOrEqual(25, 'Should have at least 25 field types');
});
