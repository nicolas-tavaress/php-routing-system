<?php

declare(strict_types=1);

namespace App\Routing;

use App\Exceptions\InvalidRouteException;

/**
 * Options shared by a set of routes: URI prefix, controller sub-namespace and middlewares.
 */
final readonly class RouteGroup
{
    private const array OPTIONS = ['prefix', 'controller', 'middlewares'];

    /**
     * @param list<string> $middlewares
     */
    public function __construct(
        public string $prefix = '/',
        public string $namespace = '',
        public array $middlewares = [],
    ) {}

    /**
     * Returns a new group nested inside this one: prefixes and namespaces are
     * concatenated, middlewares are appended.
     *
     * @param array<string, mixed> $options
     */
    public function merge(array $options): self
    {
        $unknown = array_diff(array_keys($options), self::OPTIONS);
        if ($unknown !== []) {
            throw new InvalidRouteException('Unknown route option(s): ' . implode(', ', $unknown));
        }

        $namespace = self::toNamespace(self::stringOption($options, 'controller'));

        return new self(
            prefix: Path::join($this->prefix, self::stringOption($options, 'prefix')),
            namespace: trim("{$this->namespace}\\{$namespace}", '\\'),
            middlewares: array_values(array_unique([...$this->middlewares, ...self::listOption($options, 'middlewares')])),
        );
    }

    /**
     * "client" => "Client", "admin/reports" => "Admin\Reports".
     */
    private static function toNamespace(string $controller): string
    {
        $segments = preg_split('#[\\\\/]+#', $controller, flags: PREG_SPLIT_NO_EMPTY) ?: [];

        return implode('\\', array_map(ucfirst(...), $segments));
    }

    /**
     * @param array<string, mixed> $options
     */
    private static function stringOption(array $options, string $key): string
    {
        $value = $options[$key] ?? '';

        return is_string($value)
            ? $value
            : throw new InvalidRouteException("Route option {$key} must be a string");
    }

    /**
     * @param array<string, mixed> $options
     * @return list<string>
     */
    private static function listOption(array $options, string $key): array
    {
        $value = $options[$key] ?? [];

        if (!is_array($value) || !array_all($value, static fn (mixed $item): bool => is_string($item))) {
            throw new InvalidRouteException("Route option {$key} must be a list of strings");
        }

        return array_values($value);
    }
}
