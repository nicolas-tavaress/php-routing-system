<?php

namespace app\library;

use Closure;

class Router {
    private array $routes = [];
    private array $routeOptions = [];
    private Route $route;

    public function add(
        string $uri,
        string $request,
        string $controller,
        array $wildcardAliases = []
    ): Router {
        $this->route = new Route($request, $controller, $wildcardAliases);
        $this->route->registerRouteUri(new Uri($uri));
        $this->route->registerRouteWildcard(new RouteWildcard);
        $this->route->registerGroupOptions(new RouteOptions($this->routeOptions));

        $this->routes[] = $this->route;
        return $this;
    }

    public function middlewares(array $middlewares): void {
        $options = ['middlewares' => $middlewares];
        if (!empty($this->routeOptions)) {
           $options = array_merge($this->routeOptions, ['middlewares' => $middlewares]);
        }

        $this->route->registerGroupOptions(new RouteOptions($options));
    }

    public function group(array $options, Closure $callback): void {
        $this->routeOptions = $options;
        $callback->call($this);
        $this->routeOptions = [];
    }

    public function options(array $options): void {
        if (!empty($this->routeOptions)) {
           $options = array_merge($this->routeOptions, $options);
        }

        $this->route->registerGroupOptions(new RouteOptions($options));
    }

    public function init(): mixed {
        foreach ($this->routes as $route) {
            if ($route->match()) return (new Controller())->call($route);
        }

        return (new Controller())->call(new Route('GET', 'NotFoundController:index'));
    }
}