<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;

$routes = require dirname(__DIR__) . '/app/Config/routes.php';

$router = new Router($routes);
echo $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);