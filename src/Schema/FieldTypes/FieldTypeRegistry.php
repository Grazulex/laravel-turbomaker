<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use InvalidArgumentException;

/**
 * Legacy FieldTypeRegistry Stub
 * This is a minimal stub to maintain backward compatibility
 * Real field type handling is now done by ModelSchema Enterprise
 */
final class FieldTypeRegistry
{
    private static array $types = [
        'string' => true,
        'text' => true,
        'integer' => true,
        'bigInteger' => true,
        'boolean' => true,
        'date' => true,
        'datetime' => true,
        'timestamp' => true,
        'float' => true,
        'double' => true,
        'decimal' => true,
        'json' => true,
        'uuid' => true,
        'email' => true,
        'url' => true,
        'foreignId' => true,
        'morphs' => true,
        'time' => true,
        'binary' => true,
        'longText' => true,
        'mediumText' => true,
        'mediumInteger' => true,
        'smallInteger' => true,
        'tinyInteger' => true,
        'unsignedBigInteger' => true,
    ];

    /**
     * Check if a field type is registered
     */
    public static function has(string $type): bool
    {
        return isset(self::$types[$type]);
    }

    /**
     * Get a field type stub
     */
    public static function get(string $type): FieldTypeStub
    {
        if (! self::has($type)) {
            throw new InvalidArgumentException("Field type '{$type}' not found");
        }

        return new FieldTypeStub($type);
    }

    /**
     * Get all registered types
     */
    public static function getTypes(): array
    {
        return array_keys(self::$types);
    }
}
