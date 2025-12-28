<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    public function __construct(
        private array $routes
    ) {}


public function dispatch(
    string $method,
    string $uri,
    array $get,
    array $post
): string {

    $path = parse_url($uri, PHP_URL_PATH);

    foreach ($this->routes as $route) {
        if ($route['method'] === $method && $route['path'] === $path) {

            // 🔐 vyžaduje přihlášení?
            if (($route['auth'] ?? false) === true && !Auth::check()) {
                header('Location: /login');
                exit;
            }

            // 🔐 kontrola role
            if (!empty($route['roles']) && !Auth::hasRole($route['roles'])) {
                http_response_code(403);
                return '403 – Nemáš oprávnění';
            }

            return $this->call($route['action'], $get, $post);
        }
    }

    http_response_code(404);
    return '404 – stránka nenalezena';
}
}