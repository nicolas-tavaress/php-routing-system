<?php

declare(strict_types=1);

namespace App\Routing;

use App\Enums\RouteWildcards;
use App\Exceptions\InvalidRouteException;

final class RouteCompiler
{
    private const string WILDCARD = '#\(:(\w+)\)#';
    private const string IDENTIFIER = '/^[a-zA-Z_]\w{0,31}$/';

    /**
     * Turns "/client/(:numeric)" into a regex with named groups, e.g. "#^/client/(?<clientId>[0-9]+)$#".
     *
     * Each wildcard is named after its alias; without one, it is named after the
     * preceding literal segment ("client") or, as a last resort, "param{n}".
     *
     * @param list<string> $aliases
     */
    public static function compile(string $path, array $aliases = []): CompiledRoute
    {
        /** @var list<string> $parts literal, wildcard type, literal, wildcard type, ..., literal */
        $parts = preg_split(self::WILDCARD, $path, flags: PREG_SPLIT_DELIM_CAPTURE) ?: [$path];

        $regex = '';
        $params = [];

        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                $regex .= preg_quote($part, '#');
                continue;
            }

            $wildcard = RouteWildcards::tryFrom($part)
                ?? throw new InvalidRouteException("Unknown wildcard (:{$part}) in route {$path}");

            $position = intdiv($index, 2);
            $name = self::paramName($aliases[$position] ?? null, $parts[$index - 1], $position, $params, $path);

            $params[$name] = $wildcard;
            $regex .= "(?<{$name}>{$wildcard->pattern()})";
        }

        if (count($aliases) > count($params)) {
            throw new InvalidRouteException("Route {$path} has more aliases than wildcards");
        }

        return new CompiledRoute("#^{$regex}$#", $params);
    }

    /**
     * @param array<string, RouteWildcards> $taken
     */
    private static function paramName(?string $alias, string $precedingLiteral, int $position, array $taken, string $path): string
    {
        if ($alias !== null) {
            if (preg_match(self::IDENTIFIER, $alias) !== 1 || isset($taken[$alias])) {
                throw new InvalidRouteException("Invalid or duplicated alias {$alias} in route {$path}");
            }

            return $alias;
        }

        $segment = basename(rtrim($precedingLiteral, '/'));

        return preg_match(self::IDENTIFIER, $segment) === 1 && !isset($taken[$segment])
            ? $segment
            : "param{$position}";
    }
}
