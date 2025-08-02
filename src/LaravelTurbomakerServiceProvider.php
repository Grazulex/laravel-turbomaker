<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker;

use Grazulex\LaravelTurbomaker\Console\Commands\TurboApiCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboMakeCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboSchemaCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboTestCommand;
use Grazulex\LaravelTurbomaker\Console\Commands\TurboViewCommand;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\BigIntegerFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\BinaryFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\BooleanFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\DateFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\DateTimeFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\DecimalFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\DoubleFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\EmailFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\FieldTypeRegistry;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\FloatFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\ForeignIdFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\IntegerFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\JsonFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\LongTextFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\MediumIntegerFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\MediumTextFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\MorphsFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\SmallIntegerFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\StringFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\TextFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\TimeFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\TimestampFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\TinyIntegerFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\UnsignedBigIntegerFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\UrlFieldType;
use Grazulex\LaravelTurbomaker\Schema\FieldTypes\UuidFieldType;
use Grazulex\LaravelTurbomaker\Schema\SchemaParser;
use Illuminate\Support\ServiceProvider;

final class LaravelTurbomakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/turbomaker.php',
            'turbomaker'
        );

        // Register core field types
        $this->registerFieldTypes();

        // Register custom field types from config
        $this->registerCustomFieldTypes();

        // Register schema services
        $this->app->singleton(SchemaParser::class);
        $this->app->singleton(TurboSchemaManager::class);

        // Register the main manager
        $this->app->singleton(TurbomakerManager::class, function ($app): TurbomakerManager {
            return new TurbomakerManager($app);
        });

        // Register alias
        $this->app->alias(TurbomakerManager::class, 'turbomaker');

        // Register commands in the container
        $this->app->singleton(TurboMakeCommand::class);
        $this->app->singleton(TurboViewCommand::class);
        $this->app->singleton(TurboApiCommand::class);
        $this->app->singleton(TurboTestCommand::class);
        $this->app->singleton(TurboSchemaCommand::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/turbomaker.php' => config_path('turbomaker.php'),
            ], 'turbomaker-config');

            // Publish stubs
            $this->publishes([
                __DIR__.'/../stubs' => resource_path('stubs/turbomaker'),
            ], 'turbomaker-stubs');

            // Register commands
            $this->commands([
                TurboMakeCommand::class,
                TurboViewCommand::class,
                TurboApiCommand::class,
                TurboTestCommand::class,
                TurboSchemaCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            TurbomakerManager::class,
            'turbomaker',
        ];
    }

    /**
     * Register all core field types
     */
    private function registerFieldTypes(): void
    {
        // Debug log to ensure method is called
        if (config('app.debug', false)) {
            logger('TurboMaker: Registering core field types...');
        }

        // String types
        FieldTypeRegistry::register('string', new StringFieldType());
        FieldTypeRegistry::register('text', new TextFieldType());
        FieldTypeRegistry::register('longText', new LongTextFieldType());
        FieldTypeRegistry::register('mediumText', new MediumTextFieldType());

        // Integer types
        FieldTypeRegistry::register('integer', new IntegerFieldType());
        FieldTypeRegistry::register('bigInteger', new BigIntegerFieldType());
        FieldTypeRegistry::register('unsignedBigInteger', new UnsignedBigIntegerFieldType());
        FieldTypeRegistry::register('tinyInteger', new TinyIntegerFieldType());
        FieldTypeRegistry::register('smallInteger', new SmallIntegerFieldType());
        FieldTypeRegistry::register('mediumInteger', new MediumIntegerFieldType());

        // Numeric types
        FieldTypeRegistry::register('boolean', new BooleanFieldType());
        FieldTypeRegistry::register('decimal', new DecimalFieldType());
        FieldTypeRegistry::register('float', new FloatFieldType());
        FieldTypeRegistry::register('double', new DoubleFieldType());

        // Date and time types
        FieldTypeRegistry::register('date', new DateFieldType());
        FieldTypeRegistry::register('datetime', new DateTimeFieldType());
        FieldTypeRegistry::register('timestamp', new TimestampFieldType());
        FieldTypeRegistry::register('time', new TimeFieldType());

        // Special types
        FieldTypeRegistry::register('json', new JsonFieldType());
        FieldTypeRegistry::register('uuid', new UuidFieldType());
        FieldTypeRegistry::register('email', new EmailFieldType());
        FieldTypeRegistry::register('url', new UrlFieldType());
        FieldTypeRegistry::register('foreignId', new ForeignIdFieldType());
        FieldTypeRegistry::register('morphs', new MorphsFieldType());
        FieldTypeRegistry::register('binary', new BinaryFieldType());

        // Debug log to confirm registration
        if (config('app.debug', false)) {
            $registeredCount = count(FieldTypeRegistry::getAvailableTypes());
            logger("TurboMaker: Registered {$registeredCount} field types");
        }
    }

    /**
     * Register custom field types from configuration
     */
    private function registerCustomFieldTypes(): void
    {
        $customTypes = config('turbomaker.custom_field_types', []);

        foreach ($customTypes as $type => $className) {
            if (class_exists($className)) {
                FieldTypeRegistry::register($type, new $className());
            }
        }
    }
}
