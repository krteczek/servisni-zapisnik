<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    public function __construct(
        private array $routes
    ) {}

 public function dispatch(string $uri, string $method): string
{
    $path = parse_url($uri, PHP_URL_PATH);

    foreach ($this->routes as $route) {
        if ($route['path'] === $path && $route['method'] === $method) {

            if (!empty($route['roles'])) {
                Auth::requireRole($route['roles']);
            }

            [$class, $action] = $route['handler'];
            return (new $class())->$action();
        }
    }

    http_response_code(404);
    return '404 – stránka nenalezena';
}
}