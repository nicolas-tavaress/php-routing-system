<?php

declare(strict_types=1);

namespace Tests\Fixtures\Middlewares;

use App\Contracts\MiddlewareInterface;
use App\Routing\Request;
use Closure;

/**
 * Wraps the result so tests can assert the order in which middlewares ran.
 */
final class TraceMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $name = 'trace') {}

    public function handle(Request $request, Closure $next): mixed
    {
        $result = $next($request);

        return "{$this->name}(" . (is_string($result) ? $result : '') . ')';
    }
}
