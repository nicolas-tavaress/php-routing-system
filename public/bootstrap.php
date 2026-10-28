<?php

declare(strict_types=1);

use App\Routing\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('display_errors', '1');

$router = new Router();

require dirname(__DIR__) . '/routes/web.php';

return $router;
