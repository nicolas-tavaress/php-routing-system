<?php

declare(strict_types=1);

namespace App\Routing;

final class Path
{
    /**
     * Collapses repeated slashes, removes the trailing one and guarantees a leading one.
     */
    public static function normalize(string $path): string
    {
        return $path
            |> (static fn (string $p): string => (string) preg_replace('#/+#', '/', $p))
            |> (static fn (string $p): string => trim($p, '/'))
            |> (static fn (string $p): string => "/{$p}");
    }

    public static function join(string ...$parts): string
    {
        return self::normalize(implode('/', $parts));
    }
}
