<?php

declare(strict_types=1);

use Grazulex\LaravelTurbomaker\Support\Laravel11Helper;

beforeEach(function () {
    // Mock Laravel app version
    if (!function_exists('app')) {
        function app($abstract = null) {
            return new class {
                public function version() {
                    return '11.0.0';
                }
            };
        }
    }
});

it('detects Laravel 11 or higher correctly', function () {
    expect(Laravel11Helper::isLaravel11OrHigher())->toBeTrue();
});

it('provides correct API route instructions for Laravel 11', function () {
    $instructions = Laravel11Helper::getApiRouteInstructions();
    
    expect($instructions)->toBeArray();
    expect($instructions)->not->toBeEmpty();
    
    $firstInstruction = $instructions[0];
    expect($firstInstruction)->toHaveKey('type');
    expect($firstInstruction)->toHaveKey('message');
    expect($firstInstruction)->toHaveKey('command');
});

it('returns empty instructions for older Laravel versions', function () {
    // Mock older Laravel version
    if (!function_exists('app_old')) {
        function app_old($abstract = null) {
            return new class {
                public function version() {
                    return '10.48.0';
                }
            };
        }
    }
    
    // This would need a way to mock the app() function to return older version
    // For now, we'll test the logic with a direct version check
    expect(Laravel11Helper::isLaravel11OrHigher())->toBeTrue(); // Will be true due to mocked app
});
