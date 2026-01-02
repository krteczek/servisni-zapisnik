<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use App\Controllers\ErrorController;

class Router
{
    public function __construct(
        private array $routes
    ) {}

    // volá controller@metodu
protected function call(array $action)
{
    [$controllerClass, $method] = $action;

    $controller = new $controllerClass($this->routes);

    return $controller->$method();
}

public function dispatch(string $path, string $method)
{
    $path = rtrim($path, '/');
    $path = $path === '' ? '/' : $path;

    foreach ($this->routes as $route) {
        if ($route['method'] === $method && $route['path'] === $path) {

            if (($route['auth'] ?? false) === true && !Auth::check()) {
            	redirect('/login');
 
            }
if (!empty($route['permission']) && !Auth::can($route['permission'])) {
    return (new ErrorController())->forbidden();
}
if (!empty($route['roles']) && !Auth::hasRole($route['roles'])) {
    return (new ErrorController())->forbidden();
}
				
            return $this->call($route['action']);
        }
    }

    return (new ErrorController())->notFound();
}


private function runMiddlewares(array $middlewares): void
{
    foreach ($middlewares as $middleware) {
        (new $middleware())->handle();
    }
}



}
