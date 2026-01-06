<?php
declare(strict_types=1);

ob_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


define('BASE_PATH', '/servisni-zapisnik/public');
require dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/app/Core/helpers.php';
$routes = require dirname(__DIR__) . '/app/Config/routes.php';

use App\Core\Router;
use App\Core\Session;


Session::start();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//session_destroy();
//var_dump($_SESSION);

/**
 * BASE PATH aplikace
 * /servisni-zapisnik/public
 */
$basePath = '/servisni-zapisnik/public';

if (str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

$uri = $uri ?: '/';

$router = new Router($routes);
echo $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);