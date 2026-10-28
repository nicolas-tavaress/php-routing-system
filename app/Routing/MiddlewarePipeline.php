<?php

declare(strict_types=1);

namespace App\Routing;

use App\Contracts\MiddlewareInterface;
use App\Enums\RouteMiddlewares;
use App\Exceptions\MiddlewareNotFoundException;
use Closure;

final readonly class MiddlewarePipeline
{
    /**
     * @param list<MiddlewareInterface> $middlewares
     */
    public function __construct(private array $middlewares = []) {}

    /**
     * Accepts aliases registered in RouteMiddlewares ('auth') or MiddlewareInterface class names.
     *
     * @param list<string> $aliases
     */
    public static function fromAliases(array $aliases): self
    {
        return new self(array_map(self::resolve(...), $aliases));
    }

    /**
     * Runs the middlewares in order; any of them may return early instead of calling $next.
     *
     * @param Closure(Request): mixed $destination
     */
    public function process(Request $request, Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            static fn (Closure $next, MiddlewareInterface $middleware): Closure
                => static fn (Request $request): mixed => $middleware->handle($request, $next),
            $destination,
        );

        return $pipeline($request);
    }

    private static function resolve(string $alias): MiddlewareInterface
    {
        $class = RouteMiddlewares::fromAlias($alias)?->class() ?? $alias;

        if (!is_a($class, MiddlewareInterface::class, true)) {
            throw new MiddlewareNotFoundException("Middleware {$alias} does not exist or does not implement " . MiddlewareInterface::class);
        }

        return new $class();
    }
}
