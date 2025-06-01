<?php

namespace app\library;
use app\library\Route;

class Controller {
    private function getControllerPath(Route $route, string $controller) {
        $routeOptionsInstance = $route->getRouteOptionsInstance();
        
        $controllerInstance = "app\\controllers\\{$controller}";
        if ($routeOptionsInstance && $routeOptionsInstance->optionExists('controller')) {
            $controllerInstance = "app\\controllers\\" . $routeOptionsInstance->execute('controller') . "\\{$controller}";
        }

        return $controllerInstance;
    }

    public function call(Route $route) {
        $controller = $route->controller;

        if (!str_contains($controller, ':')) {
            throw new \Exception("Colon needed to controller {$controller} in route");
        }

        [$controller, $action] = explode(':', $controller);

        $controllerInstance = $this->getControllerPath($route, $controller);

        if (!class_exists($controllerInstance)) {
            throw new \Exception("Controller {$controllerInstance} not found");
        }

        $controller = new $controllerInstance;
        if (!method_exists($controller, $action)) {
            throw new \Exception("Action {$action} not found in controller {$controller}");
        }

        if ($route->getRouteOptionsInstance()?->optionExists('middlewares')) {
            (new Middleware($route->getRouteOptionsInstance()->execute('middlewares')))->execute();
        }

        call_user_func_array([$controller, $action], $route->getRouteWildcardInstance()?->getParams() ?? []);
    }
}