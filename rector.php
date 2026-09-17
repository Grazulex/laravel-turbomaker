<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true
    )
    ->withSkip([
        // Yaml::parse() may return a scalar or null; keep the untyped parameter
        StrictArrayParamDimFetchRector::class => [
            __DIR__.'/src/Console/Commands/TurboSchemaCommand.php',
        ],
    ]);
