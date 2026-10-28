<?php

declare(strict_types=1);

namespace Tests\Fixtures\Middlewares;

use App\Contracts\MiddlewareInterface;
use App\Routing\Request;
use Closure;

final class BlockMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): mixed
    {
        return 'blocked';
    }
}
