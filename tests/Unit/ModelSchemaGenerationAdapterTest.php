<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use Grazulex\LaravelModelschema\Schema\ModelSchema;
use Grazulex\LaravelModelschema\Services\Generation\GenerationService;
use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaGenerationAdapter;
use Mockery;

describe('ModelSchemaGenerationAdapter', function () {
    test('it delegates generation to the ModelSchema GenerationService', function () {
        $service = Mockery::mock(GenerationService::class);
        $service->shouldReceive('generateAllWithStatus')
            ->once()
            ->withArgs(fn (ModelSchema $schema, array $options) => $schema->name === 'Product' && $options['model'] === true)
            ->andReturn([
                'model' => [
                    'success' => true,
                    'json' => '{"model":{}}',
                    'yaml' => 'model: {}',
                    'metadata' => ['generator' => 'model', 'model_name' => 'Product', 'table_name' => 'products'],
                ],
            ]);

        $results = (new ModelSchemaGenerationAdapter($service))->generateAll('Product');

        expect($results)->toHaveKey('model')
            ->and($results['model'])->toBe([app_path('Models/Product.php')]);
    });

    test('it returns real fragments from the GenerationService', function () {
        $results = (new ModelSchemaGenerationAdapter())->generateAll('Product', [
            'requests' => false,
            'resources' => false,
            'factory' => false,
            'tests' => false,
        ]);

        expect(array_keys($results))->toBe(['model', 'migration', 'controllers']);
    });

    test('it fails when the GenerationService returns no fragments', function () {
        $service = Mockery::mock(GenerationService::class);
        $service->shouldReceive('generateAllWithStatus')->andReturn([
            'model' => ['success' => false, 'json' => null, 'yaml' => null, 'metadata' => null],
        ]);

        (new ModelSchemaGenerationAdapter($service))->generateAll('Product');
    })->throws(Exception::class, 'model generation returned no fragments');
});
