<?php

declare(strict_types=1);

/**
 * Example configuration for custom field types
 *
 * Add this to your config/turbomaker.php file to register custom field types
 */

return [
    // ... existing configuration ...

    /*
    |--------------------------------------------------------------------------
    | Custom Field Types
    |--------------------------------------------------------------------------
    |
    | Register custom field types that extend the built-in functionality.
    | Each entry should map a type name to a class that implements
    | FieldTypeInterface.
    |
    */
    'custom_field_types' => [
        'money' => App\TurboMaker\FieldTypes\MoneyFieldType::class,
        'slug' => App\TurboMaker\FieldTypes\SlugFieldType::class,
        'color' => App\TurboMaker\FieldTypes\ColorFieldType::class,
        'coordinates' => App\TurboMaker\FieldTypes\CoordinatesFieldType::class,
    ],
];
