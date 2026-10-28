<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Contracts\MiddlewareInterface;
use App\Routing\Request;
use Closure;

final class Admin implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->header('X-Role') !== 'admin') {
            http_response_code(403);

            return 'Forbidden';
        }

        return $next($request);
    }
}
