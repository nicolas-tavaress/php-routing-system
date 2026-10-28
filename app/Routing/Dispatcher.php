<?php

declare(strict_types=1);

namespace App\Routing;

use App\Exceptions\ActionNotFoundException;
use App\Exceptions\ControllerNotFoundException;

final readonly class Dispatcher
{
    public function __construct(private string $controllerNamespace = 'App\\Controllers') {}

    /**
     * Instantiates the route controller and calls its action through the middleware pipeline.
     * Route params are passed as named arguments, so they must match the action parameter names.
     *
     * @param array<string, int|string> $params
     */
    public function dispatch(Route $route, Request $request, array $params = []): mixed
    {
        $class = $route->controllerClass($this->controllerNamespace);
        if (!class_exists($class)) {
            throw new ControllerNotFoundException("Controller {$class} not found");
        }

        $controller = new $class();
        $action = $route->action;
        if (!method_exists($controller, $action)) {
            throw new ActionNotFoundException("Action {$action} not found in controller {$class}");
        }

        return MiddlewarePipeline::fromAliases($route->group->middlewares)
            ->process($request, static fn (): mixed => $controller->{$action}(...$params));
    }
}
