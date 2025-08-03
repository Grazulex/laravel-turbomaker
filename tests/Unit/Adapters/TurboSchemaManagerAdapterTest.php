<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use Exception;
use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaAdapter;
use Grazulex\LaravelTurbomaker\Adapters\TurboSchemaManagerAdapter;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\TurboSchemaManager;
use Mockery;

describe('TurboSchemaManagerAdapter', function () {
    beforeEach(function () {
        $this->mockOriginalManager = Mockery::mock(TurboSchemaManager::class);
        $this->mockModelSchemaAdapter = Mockery::mock(ModelSchemaAdapter::class);
        $this->adapter = new TurboSchemaManagerAdapter($this->mockOriginalManager, $this->mockModelSchemaAdapter);
    });

    afterEach(function () {
        Mockery::close();
    });

    test('it enhances validation with ModelSchema capabilities', function () {
        $schema = new Schema(name: 'User');

        // Mock toModelSchema conversion - use real ModelSchema object since it's final
        $realModelSchema = new \Grazulex\LaravelModelschema\Schema\ModelSchema(
            name: 'User',
            table: 'users',
            fields: [],
            relationships: []
        );

        $this->mockModelSchemaAdapter
            ->shouldReceive('toModelSchema')
            ->with($schema)
            ->once()
            ->andReturn($realModelSchema);

        $this->mockOriginalManager
            ->shouldReceive('validateSchema')
            ->with($schema)
            ->once()
            ->andReturn([]); // No errors

        $result = $this->adapter->validateSchema($schema);

        expect($result)->toBe([]);
    });

    test('it delegates to original manager when ModelSchema validation fails', function () {
        $schema = new Schema(name: 'User');

        // Mock toModelSchema conversion that throws exception
        $this->mockModelSchemaAdapter
            ->shouldReceive('toModelSchema')
            ->with($schema)
            ->once()
            ->andThrow(new Exception('ModelSchema validation failed'));

        $this->mockOriginalManager
            ->shouldReceive('validateSchema')
            ->with($schema)
            ->once()
            ->andReturn(['Some error']);

        $result = $this->adapter->validateSchema($schema);

        expect($result)->toBe(['Some error']);
    });

    test('it enhances resolveSchema with fragment support', function () {
        $this->mockModelSchemaAdapter
            ->shouldReceive('canHandleSchema')
            ->with('user.schema.yml')
            ->once()
            ->andReturn(true);

        $this->mockModelSchemaAdapter
            ->shouldReceive('parseSchema')
            ->with('user.schema.yml')
            ->once()
            ->andReturn(new Schema(name: 'User'));

        $result = $this->adapter->resolveSchema('user.schema.yml', 'User');

        expect($result)->toBeInstanceOf(Schema::class);
        expect($result->name)->toBe('User');
    });

    test('it falls back to original manager for non-ModelSchema formats', function () {
        $this->mockModelSchemaAdapter
            ->shouldReceive('canHandleSchema')
            ->with('name:string,email:email')
            ->once()
            ->andReturn(false);

        $this->mockOriginalManager
            ->shouldReceive('resolveSchema')
            ->with('name:string,email:email', 'User')
            ->once()
            ->andReturn(new Schema(name: 'User'));

        $result = $this->adapter->resolveSchema('name:string,email:email', 'User');

        expect($result)->toBeInstanceOf(Schema::class);
    });

    test('it delegates utility methods to original manager', function () {
        // Test listSchemas
        $this->mockOriginalManager
            ->shouldReceive('listSchemas')
            ->once()
            ->andReturn(['user', 'post']);

        $schemas = $this->adapter->listSchemas();
        expect($schemas)->toBe(['user', 'post']);

        // Test schemaExists
        $this->mockOriginalManager
            ->shouldReceive('schemaExists')
            ->with('user')
            ->once()
            ->andReturn(true);

        $exists = $this->adapter->schemaExists('user');
        expect($exists)->toBeTrue();

        // Test clearCache
        $this->mockOriginalManager
            ->shouldReceive('clearCache')
            ->once();

        $this->adapter->clearCache();
    });
})->group('migration', 'adapters', 'turbo-schema-manager');
