<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use Exception;
use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaAdapter;
use Grazulex\LaravelTurbomaker\Adapters\SchemaParserAdapter;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\Schema\SchemaParser;
use Mockery;

describe('SchemaParserAdapter', function () {
    beforeEach(function () {
        $this->mockOriginalParser = Mockery::mock(SchemaParser::class);
        $this->mockModelSchemaAdapter = Mockery::mock(ModelSchemaAdapter::class);
        $this->adapter = new SchemaParserAdapter($this->mockOriginalParser, $this->mockModelSchemaAdapter);
    });

    afterEach(function () {
        Mockery::close();
    });

    test('it delegates to original parser for simple schema names', function () {
        $this->mockOriginalParser
            ->shouldReceive('parse')
            ->with('user')
            ->once()
            ->andReturn(new Schema(name: 'User'));

        $result = $this->adapter->parse('user');

        expect($result)->toBeInstanceOf(Schema::class);
        expect($result->name)->toBe('User');
    });

    test('it validates schema with ModelSchema before delegating parseArray', function () {
        $config = [
            'name' => 'User',
            'fields' => [
                'name' => ['type' => 'string'],
            ],
        ];

        $this->mockModelSchemaAdapter
            ->shouldReceive('validateSchema')
            ->with($config)
            ->once()
            ->andReturn(true);

        $this->mockOriginalParser
            ->shouldReceive('parseArray')
            ->with('User', $config)
            ->once()
            ->andReturn(new Schema(name: 'User'));

        $result = $this->adapter->parseArray('User', $config);

        expect($result)->toBeInstanceOf(Schema::class);
    });

    test('it continues with original parser when ModelSchema validation fails', function () {
        $config = [
            'name' => 'User',
            'fields' => [
                'name' => ['type' => 'string'],
            ],
        ];

        $this->mockModelSchemaAdapter
            ->shouldReceive('validateSchema')
            ->with($config)
            ->once()
            ->andThrow(new Exception('Validation failed'));

        $this->mockOriginalParser
            ->shouldReceive('parseArray')
            ->with('User', $config)
            ->once()
            ->andReturn(new Schema(name: 'User'));

        $result = $this->adapter->parseArray('User', $config);

        expect($result)->toBeInstanceOf(Schema::class);
    });

    test('it delegates all other methods to original parser', function () {
        // Test autoDiscover
        $this->mockOriginalParser
            ->shouldReceive('autoDiscover')
            ->with('User')
            ->once()
            ->andReturn(new Schema(name: 'User'));

        $result = $this->adapter->autoDiscover('User');
        expect($result)->toBeInstanceOf(Schema::class);

        // Test schemaExists
        $this->mockOriginalParser
            ->shouldReceive('exists')
            ->with('user')
            ->once()
            ->andReturn(true);

        $exists = $this->adapter->schemaExists('user');
        expect($exists)->toBeTrue();

        // Test listSchemas
        $this->mockOriginalParser
            ->shouldReceive('getAllSchemas')
            ->once()
            ->andReturn(['user', 'post']);

        $schemas = $this->adapter->listSchemas();
        expect($schemas)->toBe(['user', 'post']);

        // Test clearCache
        $this->mockOriginalParser
            ->shouldReceive('clearCache')
            ->once();

        $this->adapter->clearCache();
    });
})->group('migration', 'adapters', 'schema-parser');
