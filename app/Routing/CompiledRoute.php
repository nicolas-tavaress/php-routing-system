<?php

declare(strict_types=1);

namespace App\Routing;

use App\Enums\RouteWildcards;

final readonly class CompiledRoute
{
    /**
     * @param array<string, RouteWildcards> $params Parameter name => wildcard type, in URI order.
     */
    public function __construct(
        public string $regex,
        public array $params = [],
    ) {}

    /**
     * @return array<string, int|string>|null
     */
    #[\NoDiscard]
    public function match(string $path): ?array
    {
        if (preg_match($this->regex, $path, $matches) !== 1) {
            return null;
        }

        $values = [];
        foreach ($this->params as $name => $wildcard) {
            $values[$name] = $wildcard->cast(rawurldecode($matches[$name]));
        }

        return $values;
    }
}
