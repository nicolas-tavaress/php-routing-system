<?php

declare(strict_types=1);

namespace App\Routing;

final readonly class RouteMatch
{
    /**
     * @param array<string, int|string> $params
     */
    public function __construct(
        public Route $route,
        public array $params = [],
    ) {}
}
