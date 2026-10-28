<?php

declare(strict_types=1);

namespace App\Routing;

use App\Enums\HttpMethod;
use App\Exceptions\InvalidRouteException;

final readonly class Route
{
    /** Full path, including the group prefix. */
    public string $path;
    public string $controller;
    public string $action;
    private CompiledRoute $compiled;

    /**
     * @param string $handler "Controller:action"
     * @param list<string> $aliases Names for the wildcards, in order.
     */
    public function __construct(
        public HttpMethod $method,
        public string $uri,
        public string $handler,
        public RouteGroup $group = new RouteGroup(),
        public array $aliases = [],
    ) {
        $parts = explode(':', $handler);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new InvalidRouteException("Handler {$handler} must use the Controller:action format");
        }

        [$this->controller, $this->action] = $parts;
        $this->path = Path::join($group->prefix, $uri);
        $this->compiled = RouteCompiler::compile($this->path, $aliases);
    }

    public function withGroup(RouteGroup $group): self
    {
        return new self($this->method, $this->uri, $this->handler, $group, $this->aliases);
    }

    /**
     * @return array<string, int|string>|null Route params when the request matches, null otherwise.
     */
    #[\NoDiscard]
    public function match(Request $request): ?array
    {
        $methodMatches = $request->method === $this->method
            || ($request->method === HttpMethod::Head && $this->method === HttpMethod::Get);

        return $methodMatches ? $this->compiled->match($request->path) : null;
    }

    public function controllerClass(string $baseNamespace): string
    {
        return implode('\\', array_filter([$baseNamespace, $this->group->namespace, $this->controller]));
    }
}
