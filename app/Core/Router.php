<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ErrorController;

class Router
{
    private array $routes;
    private ViewContext $view;

    public function __construct(array $routes)
    {
        $this->routes = $routes;

        $this->view = new ViewContext();
        $this->view->isLogged = Auth::check();
        $this->view->user     = Auth::user();
        $this->view->flash = Flash::get();
        $this->view->menu     = Menu::build(
            $routes,
            rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/'
        );
    }

    public function dispatch(string $path, string $method): string
    {
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {

            $params = [];

            if (!self::match($route['path'], $path, $params)) {
                continue;
            }

            if (($route['method'] ?? 'GET') !== $method) {
                continue;
            }

            if (($route['auth'] ?? false) && !Auth::check()) {
                Url::redirect('/login');
                exit;
            }

            if (!empty($route['roles']) && !Auth::hasGlobalRole($route['roles'])) {
                return (new ErrorController($this->view))->forbidden();
            }

            $this->resolveTitle($route);

            return $this->call($route['action']);
        }

        return (new ErrorController($this->view))->notFound();
    }

    protected function call(array $action): string
    {
        [$controllerClass, $method] = $action;
        $controller = new $controllerClass($this->view);

        $reflection = new \ReflectionMethod($controller, $method);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();

            if (isset($_GET[$name])) {
                $args[] = $_GET[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Missing route parameter: $name");
            }
        }

        return $reflection->invokeArgs($controller, $args);
    }

    private function resolveTitle(array $route): void
    {
        if (isset($route['title'])) {
            $this->view->title = $route['title'];
            return;
        }

        if (isset($route['menu'], $route['submenu'])) {
            $this->view->title = $route['menu'] . ' > ' . $route['submenu'];
        } elseif (isset($route['menu'])) {
            $this->view->title = $route['menu'];
        } else {
            $this->view->title = 'Aplikace';
        }
    }

    private static function match(string $routePath, string $requestPath, array &$params): bool
    {
        $pattern = preg_replace('#\{([\w]+)\}#', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return false;
        }

        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        $_GET = array_merge($_GET, $params);

        return true;
    }
}
