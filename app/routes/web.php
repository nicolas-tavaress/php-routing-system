<?php

try {
    $router->group(['prefix' => 'client', 'controller' => 'client', 'middlewares' => ['auth', 'admin', 'e2e']], function() {
        $this->add('/(:numeric)/stores/(:numeric)', 'GET', 'StoresController:list', ['clientId', 'storeId']);
        $this->add('/(:numeric)', 'GET', 'ClientController:show', ['clientId']);
    });
    $router->group(['prefix' => 'provider', 'controller' => 'provider', 'middlewares' => []], function() {
        $this->add('/services/', 'GET', 'ServicesController:index');
    });
    $router->group(['prefix' => 'store', 'controller' => 'store', 'middlewares' => []], function() {
        $this->add('/users/', 'GET', 'UserController:index');
    });
    $router->add('/products/', 'GET', 'ProductController:index');
    $router->init();
} catch (Exception $e) {
    var_dump($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
}