<?php

declare(strict_types=1);

use Grazulex\LaravelTurbomaker\Export\OpenApiExporter;

it('reads schemas written in the core format', function () {
    $document = (new OpenApiExporter())->export('product', [
        'core' => [
            'model' => 'Product',
            'fields' => [
                'id' => ['type' => 'uuid', 'primary' => true],
                'name' => ['type' => 'string'],
                'tags' => ['type' => 'set', 'values' => ['new', 'sale']],
            ],
            'options' => ['timestamps' => false],
        ],
    ], ['server_url' => 'https://example.test/api']);

    $product = $document['components']['schemas']['Product'];

    expect($document['servers'])->toBe([['url' => 'https://example.test/api']])
        ->and($document['paths'])->toHaveKeys(['/products', '/products/{product}'])
        ->and($product['properties']['id'])->toBe(['type' => 'string', 'format' => 'uuid', 'readOnly' => true])
        ->and($product['properties']['tags'])->toBe(['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['new', 'sale']]])
        ->and($product['properties'])->not->toHaveKey('created_at')
        ->and($document['components']['schemas']['StoreProductRequest']['properties'])->not->toHaveKey('id');
});

it('maps field types to OpenAPI types', function (array $field, array $expected) {
    expect((new OpenApiExporter())->mapFieldType($field))->toBe($expected);
})->with([
    'integer' => [['type' => 'integer'], ['type' => 'integer', 'format' => 'int32']],
    'boolean' => [['type' => 'boolean', 'default' => true], ['type' => 'boolean', 'default' => true]],
    'date' => [['type' => 'date'], ['type' => 'string', 'format' => 'date']],
    'datetime' => [['type' => 'datetime', 'nullable' => true], ['type' => 'string', 'format' => 'date-time', 'nullable' => true]],
    'email' => [['type' => 'email'], ['type' => 'string', 'format' => 'email']],
    'json' => [['type' => 'json'], ['type' => 'object', 'additionalProperties' => true]],
    'float' => [['type' => 'float'], ['type' => 'number', 'format' => 'float']],
    'unknown' => [['type' => 'point', 'comment' => 'GPS'], ['type' => 'string', 'description' => 'GPS']],
]);

it('rejects a schema without fields', function () {
    (new OpenApiExporter())->export('empty', ['fields' => []]);
})->throws(InvalidArgumentException::class, 'Schema must have at least one field');
