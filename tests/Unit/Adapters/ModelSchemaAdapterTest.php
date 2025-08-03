<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use Grazulex\LaravelModelschema\Schema\ModelSchema;
use Grazulex\LaravelModelschema\Services\SchemaService;
use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaAdapter;
use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\Relationship;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Mockery;

describe('ModelSchemaAdapter', function () {
    beforeEach(function () {
        $this->mockSchemaService = Mockery::mock(SchemaService::class);
        $this->adapter = new ModelSchemaAdapter($this->mockSchemaService);
    });

    afterEach(function () {
        Mockery::close();
    });

    test('it validates schema data', function () {
        $validSchema = [
            'name' => 'User',
            'fields' => [
                'name' => ['type' => 'string'],
            ],
        ];

        $result = $this->adapter->validateSchema($validSchema);
        expect($result)->toBeTrue();

        $invalidSchema = [];
        $result = $this->adapter->validateSchema($invalidSchema);
        expect($result)->toBeFalse();
    });

    test('it can convert turbo schema to model schema format', function () {
        $field = new Field(
            name: 'name',
            type: 'string',
            nullable: false,
            length: 255,
            comment: 'User name',
            validationRules: ['required', 'max:255']
        );

        $relationship = new Relationship(
            name: 'posts',
            type: 'hasMany',
            model: 'App\Models\Post',
            foreignKey: 'user_id',
            localKey: 'id'
        );

        $schema = new Schema(
            name: 'User',
            fields: [$field],
            relationships: [$relationship],
            options: ['table' => 'users']
        );

        $result = $this->adapter->toModelSchema($schema);

        expect($result)->toBeInstanceOf(ModelSchema::class);
        expect($result->name)->toBe('User');
        expect($result->table)->toBe('users');
        expect($result->fields)->toHaveKey('name');
        expect($result->relationships)->toHaveKey('posts');
    });

    test('it handles empty schema gracefully', function () {
        $emptySchema = new Schema(
            name: 'Empty',
            fields: [],
            relationships: [],
            options: []
        );

        $result = $this->adapter->toModelSchema($emptySchema);

        expect($result)->toBeInstanceOf(ModelSchema::class);
        expect($result->name)->toBe('Empty');
        expect($result->fields)->toBeEmpty();
        expect($result->relationships)->toBeEmpty();
    });
})->group('migration', 'adapters');
