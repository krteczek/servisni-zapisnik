<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ErrorController;
use App\Controllers\UserController;
use App\Controllers\DashboardController;
class Router
{
    private array $routes;
    private ViewContext $view;

    public function __construct(array $routes)
    {
        $this->routes = $routes;

        // jeden ViewContext pro celý request
        $this->view = new ViewContext();
        $this->view->isLogged = Auth::check();
        $this->view->user     = Auth::user();
        $this->view->menu     = Menu::fromRoutes($routes);
    }

    public function dispatch(string $path, string $method): string
    {
        // normalizace cesty
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : $path;
//print_r($path);print_r($this->routes);
        foreach ($this->routes as $route) {

            if (
                ($route['method'] ?? '') !== $method ||
                ($route['path'] ?? '') !== $path
            ) {
                continue;
            }

            /* ===== AUTH ===== */
            if (($route['auth'] ?? false) === true && !Auth::check()) {
                redirect('/login');
            }

            /* ===== ROLE ===== */
            if (!empty($route['roles']) && !Auth::hasRole($route['roles'])) {
            	
                return (new ErrorController($this->view))->forbidden();
            }

            /* ===== PERMISSION ===== */
            if (!empty($route['permission']) && !Auth::can($route['permission'])) {
            	
                return (new ErrorController($this->view))->forbidden();
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
}
