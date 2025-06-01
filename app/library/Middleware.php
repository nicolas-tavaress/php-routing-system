<?php

namespace app\library;

use app\enums\RouteMiddlewares;

class Middleware {
    private string $middlewareClass;

    public function __construct(protected array $middlewares) {}

    private function middlewareExists(string $middleware): array {
        $casesMiddleware = RouteMiddlewares::cases();

        return array_filter($casesMiddleware,
        function (RouteMiddlewares $middlewareCase) use ($middleware) {
            if ($middlewareCase->name === $middleware) {
                $this->middlewareClass = $middlewareCase->value;
                return true;
            }
            return false;
        });
    }

    public function execute() {
        foreach ($this->middlewares as $middleware) {
            if (!$this->middlewareExists($middleware)) {
                throw new \Exception("Middleware {$middleware} not exists");
            }

            $class = $this->middlewareClass;
            if (!class_exists($class)) {
                throw new \Exception("Middleware class {$class} not found");
            }

            $middlewareClass = new $class();
            if (!$middlewareClass instanceof \app\interfaces\MiddlewareInterface) {
                throw new \Exception("Middleware class {$class} must implement MiddlewareInterface");
            }

            $middlewareClass->execute();
        }
    }
}