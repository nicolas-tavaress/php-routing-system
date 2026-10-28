<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Contracts\MiddlewareInterface;
use App\Routing\Request;
use Closure;

final class Auth implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->header('Authorization') === null) {
            http_response_code(401);

            return 'Unauthorized';
        }

        return $next($request);
    }
}
