<?php

declare(strict_types=1);

use Grazulex\LaravelTurbomaker\Console\Commands\TurboViewCommand;
use Illuminate\Console\Command;

it('validates model existence before generating views', function () {
    $command = new class extends TurboViewCommand {
        public function testModelExists(string $name): bool
        {
            return $this->modelExists($name);
        }
    };

    // Test with non-existent model
    expect($command->testModelExists('NonExistentModel'))->toBeFalse();
    
    // Test with potentially existing model (this would depend on the test environment)
    // We can't test with actual models without a full Laravel app context
});

it('formats model class name correctly', function () {
    $command = new class extends TurboViewCommand {
        public function testGetModelClass(string $name): string
        {
            return $this->getModelClass($name);
        }
    };

    expect($command->testGetModelClass('User'))->toBe('App\\Models\\User');
    expect($command->testGetModelClass('BlogPost'))->toBe('App\\Models\\BlogPost');
});
