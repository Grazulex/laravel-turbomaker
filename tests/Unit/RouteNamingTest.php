<?php

declare(strict_types=1);

use Illuminate\Support\Str;

it('verifies stub templates use plural kebab case routing', function () {
    $stubsDir = __DIR__ . '/../../stubs';
    
    if (!is_dir($stubsDir)) {
        $this->markTestSkipped('Stubs directory not found');
        return;
    }
    
    $stubFiles = [
        'view.create.stub',
        'view.edit.stub', 
        'view.index.stub',
        'view.show.stub',
        'controller.stub'
    ];
    
    foreach ($stubFiles as $stubFile) {
        $stubPath = "{$stubsDir}/{$stubFile}";
        
        if (file_exists($stubPath)) {
            $content = file_get_contents($stubPath);
            
            // Check that plural_kebab is used instead of kebab_name for routes
            if (str_contains($content, 'route(')) {
                expect($content)
                    ->not->toContain('{{ kebab_name }}')
                    ->and($content)->toContain('{{ plural_kebab }}');
            }
        }
    }
});

it('tests route naming logic with Laravel helpers', function () {
    // Test the logic that generates plural kebab case route names
    $testCases = [
        'User' => 'users',
        'BlogPost' => 'blog-posts',
        'ProductCategory' => 'product-categories',
        'ApiToken' => 'api-tokens',
    ];
    
    foreach ($testCases as $input => $expected) {
        $result = Str::kebab(Str::plural($input));
        expect($result)->toBe($expected);
    }
});

it('validates route consistency across stub files', function () {
    $stubsDir = __DIR__ . '/../../stubs';
    
    if (!is_dir($stubsDir)) {
        $this->markTestSkipped('Stubs directory not found');
        return;
    }
    
    $files = glob($stubsDir . '/*.stub');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        // If file contains route references, they should use plural_kebab
        if (preg_match('/route\([\'"]([^\'"]*)\./', $content, $matches)) {
            expect($content)->toContain('{{ plural_kebab }}');
        }
    }
});
