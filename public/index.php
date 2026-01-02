<?php

declare(strict_types=1);

ob_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);




require dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/../app/Core/helpers.php';


use App\Core\Session;
use App\Core\Router;

Session::start();

define('BASE_PATH', '/servisni-zapisnik/public');
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// base path aplikace


// vytažení čisté aplikační cesty
$path = parse_url($uri, PHP_URL_PATH);

if (str_starts_with($path, BASE_PATH)) {
    $path = substr($path, strlen(BASE_PATH));
}

$path = $path ?: '/';

$routes = require __DIR__ . '/../app/Config/routes.php';

$router = new Router($routes);

echo $router->dispatch($path, $method);
ob_end_flush();