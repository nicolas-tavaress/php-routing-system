<?php

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\MiddlewareInterface;
use App\Middlewares\Admin;
use App\Middlewares\Auth;

enum RouteMiddlewares: string
{
    case Auth = Auth::class;
    case Admin = Admin::class;

    /**
     * Finds a middleware by its alias used in the routes file (e.g. 'auth').
     */
    public static function fromAlias(string $alias): ?self
    {
        return array_find(
            self::cases(),
            static fn (self $case): bool => strcasecmp($case->name, $alias) === 0,
        );
    }

    /**
     * @return class-string<MiddlewareInterface>
     */
    public function class(): string
    {
        return $this->value;
    }
}
