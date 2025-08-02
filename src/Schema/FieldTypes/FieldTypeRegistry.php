<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Schema\FieldTypes;

use InvalidArgumentException;

final class FieldTypeRegistry
{
    private static array $types = [];

    /**
     * Register a field type
     */
    public static function register(string $name, FieldTypeInterface $type): void
    {
        self::$types[$name] = $type;
    }

    /**
     * Get a field type by name
     */
    public static function get(string $name): FieldTypeInterface
    {
        if (!isset(self::$types[$name])) {
            throw new InvalidArgumentException("Unknown field type: {$name}");
        }

        return self::$types[$name];
    }

    /**
     * Check if a field type exists
     */
    public static function has(string $name): bool
    {
        return isset(self::$types[$name]);
    }

    /**
     * Get all available field types
     */
    public static function getAvailableTypes(): array
    {
        return array_keys(self::$types);
    }

    /**
     * Get all registered field types
     */
    public static function all(): array
    {
        return self::$types;
    }

    /**
     * Clear all registered types (useful for testing)
     */
    public static function clear(): void
    {
        self::$types = [];
    }
}
