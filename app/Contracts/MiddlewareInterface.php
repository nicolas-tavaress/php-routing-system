<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Routing\Request;
use Closure;

interface MiddlewareInterface
{
    /**
     * Call $next($request) to continue the pipeline, or return a value to stop it.
     *
     * @param Closure(Request): mixed $next
     */
    public function handle(Request $request, Closure $next): mixed;
}
