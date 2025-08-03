<?php

declare(strict_types=1);

namespace Tests\Unit;

use Grazulex\LaravelModelschema\Services\SchemaService;

describe('ModelSchema Integration', function () {
    test('it can resolve schema service', function () {
        $service = app(SchemaService::class);

        expect($service)->toBeInstanceOf(SchemaService::class);
    });

    test('it has modelschema service provider class available', function () {
        expect(class_exists('Grazulex\\LaravelModelschema\\LaravelModelschemaServiceProvider'))->toBeTrue();
    });

    test('it can access field type registry', function () {
        $registry = \Grazulex\LaravelModelschema\Support\FieldTypeRegistry::class;

        expect(class_exists($registry))->toBeTrue();
    });
})->group('migration', 'integration');
