<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ErrorController;
use App\Controllers\UserController;
use App\Controllers\DashboardController;
use App\Core\Url;

class Router
{
    private array $routes;
    private ViewContext $view;

public function __construct(array $routes)
{
    $this->routes = $routes;
    
	 //$this->title =

    $this->view = new ViewContext();

    $this->view->isLogged = Auth::check();
    $this->view->user     = Auth::user();
    $this->view->menu = Menu::build(
    	$routes,
    	rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/'
	);
}

    public function dispatch(string $path, string $method): string
    {
        // normalizace cesty
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : $path;

        foreach ($this->routes as $route) {
        	
$routePath = $route['path'];
$params = [];

if (!self::match($routePath, $path, $params)) {
    continue;
}

if (($route['method'] ?? '') !== $method) {
    continue;
}


            /* ===== AUTH ===== */
				if (($route['auth'] ?? false) === true && !Auth::check()) {
				    Url::redirect('/login');
				    exit;
				}

            /* ===== ROLE ===== */
            if (!empty($route['roles']) && !Auth::hasRole($route['roles'])) {
            	
                return (new ErrorController($this->view))->forbidden();
            }
    if (!isset($route['title'])) {
    if (isset($route['menu'], $route['submenu'])) {
        $this->view->title = $route['menu'] . ' > ' . $route['submenu'];
    } elseif (isset($route['menu'])) {
        $this->view->title = $route['menu'];
    } else {
        $this->view->title = 'Aplikace';
    }
} else {
    $this->view->title = $route['title'];
}

            /* ===== CONTROLLER ===== */
            return $this->call($route['action']);
        }

        return (new ErrorController($this->view))->notFound();
    }

    protected function call(array $action): string
    {
        [$controllerClass, $method] = $action;

        $controller = new $controllerClass($this->view);

        return $controller->$method();
    }
    
    
    private static function match(string $routePath, string $requestPath, array &$params): bool
{
    // /users/{id}/edit → regex
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
