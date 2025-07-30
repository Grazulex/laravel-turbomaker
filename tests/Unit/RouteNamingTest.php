<?php

declare(strict_types=1);

it('verifies stub templates use plural kebab case routing', function () {
    $stubFiles = [
        'view.create.stub',
        'view.edit.stub', 
        'view.index.stub',
        'view.show.stub',
        'controller.stub'
    ];
    
    foreach ($stubFiles as $stubFile) {
        $stubPath = base_path("stubs/{$stubFile}");
        
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

it('checks route naming consistency in stub files', function () {
    $stubsDir = base_path('stubs');
    
    if (!is_dir($stubsDir)) {
        $this->markTestSkipped('Stubs directory not found');
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
