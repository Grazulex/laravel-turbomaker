<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class RouteGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        // For now, we'll just return a message about adding routes manually
        // In a full implementation, we'd modify the routes files
        return ['Routes need to be added manually to web.php and api.php'];
    }
}
