<?php

declare(strict_types=1);

session_start();
ob_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//require __DIR__ . '/../app/autoload.php';
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;

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
//var_dump($routes);
$router = new Router($routes);
//var_dump($path);
//var_dump($method);
//exit;
echo $router->dispatch($path, $method);
ob_end_flush();