<?php

declare(strict_types=1);

namespace App\Enums;

enum RouteWildcards: string
{
    case Numeric = 'numeric';
    case Alpha = 'alpha';
    case Any = 'any';

    public function pattern(): string
    {
        return match ($this) {
            self::Numeric => '[0-9]+',
            self::Alpha => '[a-z]+',
            self::Any => '[a-z0-9]+',
        };
    }

    /**
     * Converts the captured value to the most specific type for the wildcard.
     */
    public function cast(string $value): int|string
    {
        return $this === self::Numeric ? (int) $value : $value;
    }
}
