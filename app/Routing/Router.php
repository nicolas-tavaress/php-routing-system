<?php

declare(strict_types=1);

namespace App\Routing;

use App\Enums\HttpMethod;
use App\Exceptions\InvalidRouteException;
use Closure;

final class Router
{
    /** @var list<Route> */
    public private(set) array $routes = [];

    /** Handler ("Controller:action") used when no route matches. */
    public string $notFoundHandler = 'NotFoundController:index' {
        set {
            if (substr_count($value, ':') !== 1) {
                throw new InvalidRouteException("Handler {$value} must use the Controller:action format");
            }
            $this->notFoundHandler = $value;
        }
    }

    private RouteGroup $group;

    public function __construct(private readonly Dispatcher $dispatcher = new Dispatcher())
    {
        $this->group = new RouteGroup();
    }

    /**
     * @param list<string> $aliases Names for the route wildcards, in order.
     */
    public function add(string $uri, HttpMethod|string $method, string $handler, array $aliases = []): self
    {
        $method = is_string($method) ? HttpMethod::fromString($method) : $method;
        $this->routes[] = new Route($method, $uri, $handler, $this->group, $aliases);

        return $this;
    }

    /**
     * Appends middlewares to the last added route.
     *
     * @param list<string> $middlewares
     */
    public function middlewares(array $middlewares): self
    {
        return $this->options(['middlewares' => $middlewares]);
    }

    /**
     * Merges options (prefix, controller, middlewares) into the last added route.
     *
     * @param array<string, mixed> $options
     */
    public function options(array $options): self
    {
        $route = array_pop($this->routes)
            ?? throw new \LogicException('Call add() before configuring a route');

        $this->routes[] = $route->withGroup($route->group->merge($options));

        return $this;
    }

    /**
     * Registers the routes added inside $callback with the given options. Groups can be nested.
     * The callback receives the router both as $this and as its first argument.
     *
     * @param array<string, mixed> $options
     * @param Closure(Router): mixed $callback
     * @param-closure-this Router $callback
     */
    public function group(array $options, Closure $callback): void
    {
        $parent = $this->group;
        $this->group = $parent->merge($options);

        try {
            $callback->call($this, $this);
        } finally {
            $this->group = $parent;
        }
    }

    #[\NoDiscard]
    public function match(Request $request): ?RouteMatch
    {
        foreach ($this->routes as $route) {
            $params = $route->match($request);
            if ($params !== null) {
                return new RouteMatch($route, $params);
            }
        }

        return null;
    }

    /**
     * Dispatches the request (defaults to the current one) and returns the action result.
     */
    public function init(?Request $request = null): mixed
    {
        $request ??= Request::fromGlobals();
        $match = $this->match($request)
            ?? new RouteMatch(new Route(HttpMethod::Get, '/', $this->notFoundHandler));

        return $this->dispatcher->dispatch($match->route, $request, $match->params);
    }
}
