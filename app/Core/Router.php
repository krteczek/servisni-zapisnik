<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Router
{
    public function __construct(
        private array $routes
    ) {}

    // volá controller@metodu
protected function call(array $action): string
{
	//print_r($action);
    [$controllerClass, $method] = $action;

    $controller = new $controllerClass();

    return $controller->$method();
}

public function dispatch(string $path, string $method): string
{
    $path = rtrim($path, '/');
    $path = $path === '' ? '/' : $path;
	 //var_dump($path);exit;
	 $loginPath = BASE_PATH . '/login';
	 //var_dump($loginPath);exit;
    foreach ($this->routes as $route) {
        if ($route['method'] === $method && $route['path'] === $path) {

            if (($route['auth'] ?? false) === true && !Auth::check()) {
                header('Location: ' . $loginPath);
                exit;
            }

            if (!empty($route['roles']) && !Auth::hasRole($route['roles'])) {
                http_response_code(403);
                return '403 – Nemáš oprávnění';
            }
				//var_dump($route['action']);exit;
            return $this->call($route['action']);
        }
    }
	var_dump($route['action']);exit;
    http_response_code(404);
    return '404 – stránka nenalezena';
}
}
