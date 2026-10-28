<?php

declare(strict_types=1);

use App\Routing\Router;

/** @var Router $router */

$router->group(['prefix' => 'client', 'controller' => 'client', 'middlewares' => ['auth', 'admin']], function (Router $router) {
    $router->add('/(:numeric)/stores/(:numeric)', 'GET', 'StoresController:list', ['clientId', 'storeId']);
    $router->add('/(:numeric)', 'GET', 'ClientController:show', ['clientId']);
});

$router->group(['prefix' => 'provider', 'controller' => 'provider'], function (Router $router) {
    $router->add('/services/', 'GET', 'ServicesController:index');
});

$router->group(['prefix' => 'store', 'controller' => 'store'], function (Router $router) {
    $router->add('/users/', 'GET', 'UserController:index');
});

$router->add('/products/', 'GET', 'ProductController:index');
