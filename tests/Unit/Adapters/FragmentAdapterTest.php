<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use Grazulex\LaravelModelschema\Services\Generation\GenerationService;
use Grazulex\LaravelTurbomaker\Adapters\FragmentAdapter;
use Grazulex\LaravelTurbomaker\Adapters\ModelSchemaAdapter;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\Schema\Field;
use Tests\TestCase;
use Mockery;

describe('FragmentAdapter', function () {
    beforeEach(function () {
        $this->mockGenerationService = Mockery::mock(GenerationService::class);
        $this->mockModelSchemaAdapter = Mockery::mock(ModelSchemaAdapter::class);
        $this->adapter = new FragmentAdapter($this->mockGenerationService, $this->mockModelSchemaAdapter);
    });

    afterEach(function () {
        Mockery::close();
    });

    test('it can generate fillable fragment for model', function () {
        $fields = [
            'name' => new Field(name: 'name', type: 'string'),
            'email' => new Field(name: 'email', type: 'email'),
            'created_at' => new Field(name: 'created_at', type: 'created_at'),
            'updated_at' => new Field(name: 'updated_at', type: 'updated_at'),
            'id' => new Field(name: 'id', type: 'id'),
        ];
        
        $schema = new Schema(name: 'User', fields: $fields);
        
        $fillable = $this->adapter->generateFillableFragment($schema);
        
        expect($fillable)->toBeArray();
        expect($fillable)->toContain('name');
        expect($fillable)->toContain('email');
        expect($fillable)->not->toContain('created_at');
        expect($fillable)->not->toContain('updated_at');
        expect($fillable)->not->toContain('id');
    });

    test('it can generate casts fragment for model', function () {
        $fields = [
            'name' => new Field(name: 'name', type: 'string'),
            'is_active' => new Field(name: 'is_active', type: 'boolean'),
            'price' => new Field(name: 'price', type: 'decimal'),
            'settings' => new Field(name: 'settings', type: 'json'),
            'created_at' => new Field(name: 'created_at', type: 'dateTime'),
        ];
        
        $schema = new Schema(name: 'Product', fields: $fields);
        
        $casts = $this->adapter->generateCastsFragment($schema);
        
        expect($casts)->toBeArray();
        expect($casts['is_active'])->toBe('boolean');
        expect($casts['price'])->toBe('decimal:2');
        expect($casts['settings'])->toBe('array');
        expect($casts['created_at'])->toBe('datetime');
        expect($casts)->not->toHaveKey('name'); // String doesn't need casting
    });

    test('it can generate validation fragment', function () {
        $fields = [
            'name' => new Field(
                name: 'name', 
                type: 'string', 
                validationRules: ['required', 'max:255']
            ),
            'email' => new Field(
                name: 'email', 
                type: 'email', 
                validationRules: ['required', 'email', 'unique:users']
            ),
            'age' => new Field(
                name: 'age', 
                type: 'integer'
            ),
        ];
        
        $schema = new Schema(name: 'User', fields: $fields);
        
        $validation = $this->adapter->generateValidationFragment($schema);
        
        expect($validation)->toBeArray();
        expect($validation['name'])->toBe(['required', 'max:255']);
        expect($validation['email'])->toBe(['required', 'email', 'unique:users']);
        expect($validation)->not->toHaveKey('age'); // No validation rules
    });

    test('it can convert fragment format', function () {
        $turboFragment = [
            'fields' => ['name' => ['type' => 'string']],
            'relationships' => ['posts' => ['type' => 'hasMany']],
            'table' => 'users',
            'model' => 'User',
        ];
        
        $converted = $this->adapter->convertFragmentFormat($turboFragment);
        
        expect($converted)->toBeArray();
        expect($converted)->toHaveKey('fields');
        expect($converted)->toHaveKey('relationships');
        expect($converted)->toHaveKey('table');
        expect($converted)->toHaveKey('model');
        expect($converted['fields'])->toBe(['name' => ['type' => 'string']]);
    });
})->group('migration', 'adapters', 'fragments');
