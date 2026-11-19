<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Export;

use Illuminate\Support\Str;
use InvalidArgumentException;
use stdClass;

/**
 * Builds an OpenAPI 3.0 document describing the REST API generated
 * by turbo:make / turbo:api for a schema (Route::apiResource routes,
 * API resource responses and form request bodies).
 */
final class OpenApiExporter
{
    public const OPENAPI_VERSION = '3.0.3';

    private const PRIMARY_KEY = 'id';

    private const TIMESTAMP_FIELDS = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @param  string  $name  Schema name as given on the command line
     * @param  array<string, mixed>  $schema  Parsed content of the schema file
     * @param  array{server_url?: string|null, title?: string|null, version?: string|null}  $options
     * @return array<string, mixed>
     */
    public function export(string $name, array $schema, array $options = []): array
    {
        $definition = $this->normalizeDefinition($schema);
        $fields = $definition['fields'];

        if ($fields === []) {
            throw new InvalidArgumentException('Schema must have at least one field');
        }

        $model = $this->resolveModelName($name, $definition['model']);
        $segment = Str::kebab(Str::plural($model));
        $parameter = Str::snake($model);
        $tag = Str::headline(Str::plural($model));

        $document = [
            'openapi' => self::OPENAPI_VERSION,
            'info' => [
                'title' => $options['title'] ?? "{$model} API",
                'version' => $options['version'] ?? (string) ($schema['metadata']['version'] ?? '1.0.0'),
            ],
        ];

        $description = $schema['metadata']['description'] ?? null;
        if (is_string($description) && $description !== '') {
            $document['info']['description'] = $description;
        }

        $document['servers'] = [
            ['url' => ($options['server_url'] ?? null) ?: '/api'],
        ];
        $document['tags'] = [['name' => $tag]];
        $document['paths'] = $this->buildPaths($model, $segment, $parameter, $tag);
        $document['components'] = [
            'schemas' => [
                $model => $this->buildResourceSchema($fields, $definition['options']),
                "{$model}Collection" => $this->buildCollectionSchema($model),
                "Store{$model}Request" => $this->buildInputSchema($fields, true),
                "Update{$model}Request" => $this->buildInputSchema($fields, false),
                'ValidationError' => $this->buildValidationErrorSchema(),
            ],
            'responses' => [
                'NotFound' => [
                    'description' => 'Resource not found',
                ],
                'ValidationError' => [
                    'description' => 'Validation failed',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ValidationError'],
                        ],
                    ],
                ],
            ],
        ];

        return $document;
    }

    /**
     * Map a schema field type to an OpenAPI schema object.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    public function mapFieldType(array $field, bool $forInput = false): array
    {
        $type = mb_strtolower((string) ($field['type'] ?? 'string'));

        $schema = match ($type) {
            'integer', 'int', 'smallinteger', 'tinyinteger', 'mediuminteger',
            'unsignedinteger', 'unsignedsmallinteger', 'unsignedtinyinteger',
            'unsignedmediuminteger', 'increments', 'year' => ['type' => 'integer', 'format' => 'int32'],
            'biginteger', 'bigint', 'long', 'unsignedbiginteger', 'bigincrements',
            'foreignid', 'foreign_id', 'fk', 'id' => ['type' => 'integer', 'format' => 'int64'],
            'float' => ['type' => 'number', 'format' => 'float'],
            'double', 'real' => ['type' => 'number', 'format' => 'double'],
            // Decimals are cast to strings ("12.50") in the JSON responses
            'decimal', 'numeric', 'money', 'unsigneddecimal' => $forInput
                ? ['type' => 'number']
                : ['type' => 'string', 'format' => 'decimal'],
            'boolean', 'bool' => ['type' => 'boolean'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'datetime', 'datetimetz', 'timestamp', 'timestamptz' => ['type' => 'string', 'format' => 'date-time'],
            'time', 'timetz' => ['type' => 'string', 'format' => 'time'],
            'email', 'email_address' => ['type' => 'string', 'format' => 'email'],
            'url' => ['type' => 'string', 'format' => 'uri'],
            'uuid', 'guid', 'foreignuuid' => ['type' => 'string', 'format' => 'uuid'],
            'password' => ['type' => 'string', 'format' => 'password'],
            'binary' => ['type' => 'string', 'format' => 'byte'],
            'json', 'jsonb', 'array', 'object' => ['type' => 'object', 'additionalProperties' => true],
            'set' => ['type' => 'array', 'items' => ['type' => 'string']],
            'enum' => ['type' => 'string'],
            default => ['type' => 'string'],
        };

        $values = $field['values'] ?? $field['options'] ?? null;
        if (in_array($type, ['enum', 'set'], true) && is_array($values) && $values !== []) {
            $values = array_values(array_map(static fn ($value): string => (string) $value, $values));
            if ($type === 'enum') {
                $schema['enum'] = $values;
            } else {
                $schema['items']['enum'] = $values;
            }
        }

        $length = $field['length'] ?? null;
        if ($schema['type'] === 'string' && ! isset($schema['format']) && (is_int($length) || (is_string($length) && ctype_digit($length)))) {
            $schema['maxLength'] = (int) $length;
        }

        if (($field['nullable'] ?? false) === true) {
            $schema['nullable'] = true;
        }

        if (array_key_exists('default', $field) && $field['default'] !== null && ! is_array($field['default'])) {
            $schema['default'] = $field['default'];
        }

        $description = $field['description'] ?? $field['comment'] ?? null;
        if (is_string($description) && $description !== '') {
            $schema['description'] = $description;
        }

        return $schema;
    }

    /**
     * Accept both the flat format (fields/options at the root) and the
     * "core:" format used by ModelSchema.
     *
     * @param  array<string, mixed>  $schema
     * @return array{model: string|null, fields: array<string, array<string, mixed>>, options: array<string, mixed>}
     */
    private function normalizeDefinition(array $schema): array
    {
        $root = isset($schema['core']) && is_array($schema['core']) ? $schema['core'] : $schema;

        $fields = [];
        foreach ((array) ($root['fields'] ?? []) as $fieldName => $field) {
            if (! is_string($fieldName)) {
                continue;
            }
            $fields[$fieldName] = is_array($field) ? $field : ['type' => (string) $field];
        }

        $model = $root['model'] ?? null;

        return [
            'model' => is_string($model) && $model !== '' ? $model : null,
            'fields' => $fields,
            'options' => is_array($root['options'] ?? null) ? $root['options'] : [],
        ];
    }

    private function resolveModelName(string $name, ?string $model): string
    {
        if ($model !== null) {
            return class_basename($model);
        }

        return Str::studly($name);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPaths(string $model, string $segment, string $parameter, string $tag): array
    {
        $ref = "#/components/schemas/{$model}";
        $single = [
            'type' => 'object',
            'properties' => [
                'data' => ['$ref' => $ref],
            ],
        ];
        $jsonResponse = static fn (string $description, array $schema): array => [
            'description' => $description,
            'content' => ['application/json' => ['schema' => $schema]],
        ];
        $requestBody = static fn (string $schemaName): array => [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => ['$ref' => "#/components/schemas/{$schemaName}"],
                ],
            ],
        ];
        $notFound = ['$ref' => '#/components/responses/NotFound'];
        $invalid = ['$ref' => '#/components/responses/ValidationError'];
        $plural = Str::plural($model);

        $updateOperation = static fn (string $operationId, string $summary): array => [
            'tags' => [$tag],
            'summary' => $summary,
            'operationId' => $operationId,
            'requestBody' => $requestBody("Update{$model}Request"),
            'responses' => [
                '200' => $jsonResponse("{$model} updated", $single),
                '404' => $notFound,
                '422' => $invalid,
            ],
        ];

        return [
            "/{$segment}" => [
                'get' => [
                    'tags' => [$tag],
                    'summary' => "List {$plural}",
                    'operationId' => "list{$plural}",
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'integer', 'minimum' => 1],
                        ],
                    ],
                    'responses' => [
                        '200' => $jsonResponse("Paginated list of {$plural}", ['$ref' => "#/components/schemas/{$model}Collection"]),
                    ],
                ],
                'post' => [
                    'tags' => [$tag],
                    'summary' => "Create a {$model}",
                    'operationId' => "create{$model}",
                    'requestBody' => $requestBody("Store{$model}Request"),
                    'responses' => [
                        '201' => $jsonResponse("{$model} created", $single),
                        '422' => $invalid,
                    ],
                ],
            ],
            "/{$segment}/{{$parameter}}" => [
                'parameters' => [
                    [
                        'name' => $parameter,
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'get' => [
                    'tags' => [$tag],
                    'summary' => "Show a {$model}",
                    'operationId' => "show{$model}",
                    'responses' => [
                        '200' => $jsonResponse($model, $single),
                        '404' => $notFound,
                    ],
                ],
                'put' => $updateOperation("update{$model}", "Update a {$model}"),
                'patch' => $updateOperation("patch{$model}", "Partially update a {$model}"),
                'delete' => [
                    'tags' => [$tag],
                    'summary' => "Delete a {$model}",
                    'operationId' => "delete{$model}",
                    'responses' => [
                        '204' => ['description' => "{$model} deleted"],
                        '404' => $notFound,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function buildResourceSchema(array $fields, array $options): array
    {
        $properties = [];
        $required = [];

        if (! isset($fields[self::PRIMARY_KEY])) {
            $properties[self::PRIMARY_KEY] = ['type' => 'integer', 'format' => 'int64', 'readOnly' => true];
            $required[] = self::PRIMARY_KEY;
        }

        foreach ($fields as $fieldName => $field) {
            $property = $this->mapFieldType($field);
            if ($this->isPrimary($fieldName, $field)) {
                $property['readOnly'] = true;
            }
            $properties[$fieldName] = $property;

            if (($field['nullable'] ?? false) !== true) {
                $required[] = $fieldName;
            }
        }

        if (($options['timestamps'] ?? true) !== false) {
            foreach (['created_at', 'updated_at'] as $timestamp) {
                $properties[$timestamp] ??= ['type' => 'string', 'format' => 'date-time', 'nullable' => true, 'readOnly' => true];
            }
        }

        if (($options['soft_deletes'] ?? $options['softDeletes'] ?? false) === true) {
            $properties['deleted_at'] ??= ['type' => 'string', 'format' => 'date-time', 'nullable' => true, 'readOnly' => true];
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];

        if ($required !== []) {
            $schema['required'] = array_values(array_unique($required));
        }

        return $schema;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function buildInputSchema(array $fields, bool $withRequired): array
    {
        $properties = [];
        $required = [];

        foreach ($fields as $fieldName => $field) {
            if ($this->isPrimary($fieldName, $field) || in_array($fieldName, self::TIMESTAMP_FIELDS, true)) {
                continue;
            }

            $properties[$fieldName] = $this->mapFieldType($field, true);

            if (($field['nullable'] ?? false) !== true && ! array_key_exists('default', $field)) {
                $required[] = $fieldName;
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties === [] ? new stdClass() : $properties,
        ];

        if ($withRequired && $required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCollectionSchema(string $model): array
    {
        $nullableString = ['type' => 'string', 'nullable' => true];

        return [
            'type' => 'object',
            'properties' => [
                'data' => [
                    'type' => 'array',
                    'items' => ['$ref' => "#/components/schemas/{$model}"],
                ],
                'links' => [
                    'type' => 'object',
                    'properties' => [
                        'first' => $nullableString,
                        'last' => $nullableString,
                        'prev' => $nullableString,
                        'next' => $nullableString,
                    ],
                ],
                'meta' => [
                    'type' => 'object',
                    'properties' => [
                        'current_page' => ['type' => 'integer'],
                        'from' => ['type' => 'integer', 'nullable' => true],
                        'last_page' => ['type' => 'integer'],
                        'path' => ['type' => 'string'],
                        'per_page' => ['type' => 'integer'],
                        'to' => ['type' => 'integer', 'nullable' => true],
                        'total' => ['type' => 'integer'],
                    ],
                    'additionalProperties' => true,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildValidationErrorSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string'],
                'errors' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function isPrimary(string $fieldName, array $field): bool
    {
        return $fieldName === self::PRIMARY_KEY || ($field['primary'] ?? false) === true;
    }
}
