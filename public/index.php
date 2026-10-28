<?php

declare(strict_types=1);

use App\Exceptions\RoutingException;
use App\Exceptions\UnsupportedMethodException;
use App\Routing\Router;

/** @var Router $router */
$router = require __DIR__ . '/bootstrap.php';

try {
    $response = $router->init();
} catch (UnsupportedMethodException) {
    http_response_code(405);
    $response = 'Method Not Allowed';
} catch (RoutingException $e) {
    http_response_code(500);
    $response = $e->getMessage();
}

if (is_array($response) || $response instanceof JsonSerializable) {
    header('Content-Type: application/json');
    $response = json_encode($response, JSON_THROW_ON_ERROR);
}

echo is_scalar($response) || $response instanceof Stringable ? (string) $response : '';
