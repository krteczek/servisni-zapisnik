<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ErrorController;
use Throwable;
use App\Core\LoggerHolder;

final class Router
{
    private array $routes = [];
    private ViewContext $view;

    public function __construct(array $routes)
    {
        // shared view context
        $this->view = new ViewContext();
        $this->view->isLogged = Auth::check();
        $this->view->user     = Auth::user();

        foreach ($routes as $route) {
            $this->routes[] = [
                ...$route,
                'method' => strtoupper($route['method'] ?? 'GET'),
                'regex'  => $this->compilePath($route['path']),
            ];
        }

        // menu
        $this->view->menu = Menu::build(
            $routes,
            rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/'
        );
    }

    public function dispatch(string $uri, string $method): string
    {
        $path = rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';

        foreach ($this->routes as $route) {

            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            // auth
            if (($route['auth'] ?? false) && !Auth::check()) {
                Url::redirect('/login');
                exit;
            }

            // roles
            if (!empty($route['roles']) && !Auth::hasGlobalRole($route['roles'])) {
                return (new ErrorController($this->view))->forbidden();
            }

            // params
            $params = [];
            foreach ($matches as $k => $v) {
                if (is_string($k)) {
                    $params[$k] = $v;
                }
            }

            $this->resolveTitle($route);

            try {
                return $this->call($route['action'], $params);
            } catch (Throwable $e) {
                return $this->handleError(500, $e);
            }
        }

        return (new ErrorController($this->view))->notFound();
    }

    private function call(array $action, array $params): string
    {
        [$class, $method] = $action;
        $controller = new $class($this->view);

        //return $controller->$method(...array_values($params));
        $args = [];

foreach ($params as $value) {
    if (ctype_digit($value)) {
        $args[] = (int) $value;
    } else {
        $args[] = $value;
    }
}

return $controller->$method(...$args);

    }



private function handleError(int $code, ?Throwable $e = null): string
{
    if ($e !== null) {
        LoggerHolder::get()->error(
            'Router exception',
            [
                'code'    => $code,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]
        );
    }

    $view  = new ViewContext();
    $error = new ErrorController($view);

    return $error->renderError($code, $e);
}

    private function resolveTitle(array $route): void
    {
        if (isset($route['title'])) {
            $this->view->title = $route['title'];
        } elseif (isset($route['menu'], $route['submenu'])) {
            $this->view->title = "{$route['menu']} > {$route['submenu']}";
        } elseif (isset($route['menu'])) {
            $this->view->title = $route['menu'];
        } else {
            $this->view->title = 'Aplikace';
        }
    }

    private function compilePath(string $path): string
    {
        $regex = preg_replace_callback(
            '#\{(\w+)(?::([^}]+))?\}#',
            fn($m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
            $path
        );

        return '#^' . $regex . '$#';
    }
}
