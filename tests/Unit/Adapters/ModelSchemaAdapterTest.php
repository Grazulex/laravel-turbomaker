<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use Grazulex\LaravelModelschema\Services\SchemaService;
use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaAdapter;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\Relationship;
use Tests\TestCase;
use Mockery;

describe('ModelSchemaAdapter', function () {
    beforeEach(function () {
        $this->mockSchemaService = Mockery::mock(SchemaService::class);
        $this->adapter = new ModelSchemaAdapter($this->mockSchemaService);
    });

    afterEach(function () {
        Mockery::close();
    });

    test('it can convert turbo schema to model schema format', function () {
        $field = new Field(
            name: 'name',
            type: 'string',
            nullable: false,
            unique: false,
            index: false,
            default: null,
            length: 255,
            comment: 'User name',
            attributes: [],
            validationRules: ['required', 'max:255'],
            factoryRules: []
        );

        $relationship = new Relationship(
            'posts',
            'hasMany',
            'App\\Models\\Post',
            'user_id',
            'id'
        );

        $schema = new Schema(
            name: 'User',
            fields: ['name' => $field],
            relationships: ['posts' => $relationship],
            options: []
        );

        $result = $this->adapter->toModelSchema($schema);

        expect($result)->toBeArray();
        expect($result['name'])->toBe('User');
        expect($result)->toHaveKey('fields');
        expect($result)->toHaveKey('relationships');
        
        // Check field conversion
        expect($result['fields']['name']['type'])->toBe('string');
        expect($result['fields']['name']['nullable'])->toBe(false);
        expect($result['fields']['name']['length'])->toBe(255);
        expect($result['fields']['name']['comment'])->toBe('User name');
        expect($result['fields']['name']['validation'])->toBe(['required', 'max:255']);
        
        // Check relationship conversion
        expect($result['relationships']['posts']['type'])->toBe('hasMany');
        expect($result['relationships']['posts']['model'])->toBe('App\\Models\\Post');
        expect($result['relationships']['posts']['foreign_key'])->toBe('user_id');
        expect($result['relationships']['posts']['local_key'])->toBe('id');
    });

    test('it can convert model schema format to turbo schema', function () {
        $modelSchemaData = [
            'name' => 'User',
            'table' => 'users',
            'fields' => [
                'name' => [
                    'type' => 'string',
                    'nullable' => false,
                    'length' => 255,
                    'validation' => ['required', 'max:255']
                ],
                'email' => [
                    'type' => 'email',
                    'nullable' => false,
                    'unique' => true
                ]
            ],
            'relationships' => [
                'posts' => [
                    'type' => 'hasMany',
                    'model' => 'App\\Models\\Post',
                    'foreign_key' => 'user_id',
                    'local_key' => 'id'
                ]
            ]
        ];

        $result = $this->adapter->fromModelSchema($modelSchemaData, 'User');

        expect($result)->toBeInstanceOf(Schema::class);
        expect($result->name)->toBe('User');
        
        // Check fields
        expect($result->fields)->toHaveCount(2);
        expect($result->fields)->toHaveKey('name');
        expect($result->fields)->toHaveKey('email');
        
        $nameField = $result->fields['name'];
        expect($nameField->type)->toBe('string');
        expect($nameField->nullable)->toBe(false);
        expect($nameField->length)->toBe(255);
        expect($nameField->validationRules)->toBe(['required', 'max:255']);
        
        $emailField = $result->fields['email'];
        expect($emailField->type)->toBe('email');
        expect($emailField->unique)->toBe(true);
        
        // Check relationships
        expect($result->relationships)->toHaveCount(1);
        expect($result->relationships)->toHaveKey('posts');
        
        $postsRelationship = $result->relationships['posts'];
        expect($postsRelationship->type)->toBe('hasMany');
        expect($postsRelationship->model)->toBe('App\\Models\\Post');
    });

    test('it validates schema data', function () {
        // Valid schema
        $validSchema = [
            'name' => 'User',
            'fields' => [
                'name' => ['type' => 'string']
            ]
        ];
        
        expect($this->adapter->validateSchema($validSchema))->toBeTrue();

        // Invalid schema (missing name)
        $invalidSchema = [
            'fields' => [
                'name' => ['type' => 'string']
            ]
        ];
        
        expect($this->adapter->validateSchema($invalidSchema))->toBeFalse();

        // Invalid field (missing type)
        $invalidFieldSchema = [
            'name' => 'User',
            'fields' => [
                'name' => ['nullable' => false]
            ]
        ];
        
        expect($this->adapter->validateSchema($invalidFieldSchema))->toBeFalse();
    });

    test('it handles empty schema gracefully', function () {
        $emptySchema = [
            'name' => 'EmptyModel'
        ];

        $result = $this->adapter->fromModelSchema($emptySchema, 'EmptyModel');

        expect($result)->toBeInstanceOf(Schema::class);
        expect($result->name)->toBe('EmptyModel');
        expect($result->fields)->toBeEmpty();
        expect($result->relationships)->toBeEmpty();
    });
})->group('migration', 'adapters');
