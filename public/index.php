<?php
declare(strict_types=1);

ob_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', '/servisni-zapisnik/public');

require dirname(__DIR__) . '/bootstrap.php';

$routes = require dirname(__DIR__) . '/app/Config/routes.php';

use App\Core\Router;
use App\Core\Session;
use App\Core\Logger;

Session::start();

$logger = new Logger(dirname(__DIR__) . '/storage/logs/app.log');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// odstranění base path
if (str_starts_with($uri, BASE_PATH)) {
    $uri = substr($uri, strlen(BASE_PATH));
}

$uri = $uri ?: '/';

try {
    $router = new Router($routes);
    echo $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
} catch (Throwable $e) {

    http_response_code(500);

    $logger->error(
        $e->getMessage(),
        [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]
    );

    if (ini_get('display_errors')) {
        echo '<pre>' . $e . '</pre>';
    } else {
        echo 'Internal Server Error';
    }
}