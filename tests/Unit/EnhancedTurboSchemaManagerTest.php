<?php

declare(strict_types=1);

namespace Tests\Unit;

use Grazulex\LaravelTurbomaker\Schema\Field;
use Grazulex\LaravelTurbomaker\Schema\Schema;
use Grazulex\LaravelTurbomaker\TurboSchemaManager;

describe('Enhanced TurboSchemaManager', function () {
    beforeEach(function () {
        $this->manager = new TurboSchemaManager();
    });

    test('it provides enhanced validation with ModelSchema capabilities', function () {
        $schema = new Schema(name: 'User', fields: [
            'name' => new Field(name: 'name', type: 'string'),
            'email' => new Field(name: 'email', type: 'email'),
        ]);

        $errors = $this->manager->validateSchema($schema);

        // Should have no errors for valid schema
        expect($errors)->toBe([]);
    });

    test('it detects invalid field types in enhanced validation', function () {
        $schema = new Schema(name: 'User', fields: [
            'invalid_field' => new Field(name: 'invalid_field', type: 'invalid_type'),
        ]);

        $errors = $this->manager->validateSchema($schema);

        // Should detect the invalid field type
        expect($errors)->toContain("Invalid field type 'invalid_type' for field 'invalid_field'");
    });

    test('it can resolve schemas with ModelSchema enhancement when available', function () {
        // Test with shorthand syntax (should work as before)
        $schema = $this->manager->resolveSchema('name:string,email:email', 'User');

        expect($schema)->toBeInstanceOf(Schema::class);
        expect($schema->name)->toBe('User');
        expect($schema->fields)->toHaveCount(2);
        expect($schema->fields['name']->type)->toBe('string');
        expect($schema->fields['email']->type)->toBe('email');
    });

    test('it maintains backward compatibility for all existing functionality', function () {
        // Test schema existence check
        $exists = $this->manager->schemaExists('nonexistent-schema');
        expect($exists)->toBeFalse();

        // Test listing schemas
        $schemas = $this->manager->listSchemas();
        expect($schemas)->toBeArray();

        // Test cache clearing (should not throw)
        $this->manager->clearCache();
        expect(true)->toBeTrue(); // If we get here, no exception was thrown
    });

    test('it can create schema files with enhanced metadata', function () {
        $fields = [
            'name' => ['type' => 'string', 'required' => true],
            'email' => ['type' => 'email', 'unique' => true],
        ];

        $relationships = [
            'posts' => ['type' => 'hasMany', 'model' => 'Post'],
        ];

        $path = $this->manager->createSchemaFile('TestModel', $fields, $relationships);

        expect($path)->toBeString();
        expect($path)->toContain('test_model.schema.yml');
    });
})->group('migration', 'enhanced-manager');
