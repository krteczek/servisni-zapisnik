<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected ViewContext $view;

    public function __construct(array $routes)
    {
        $this->view = new ViewContext();

        // základní globální data
        $this->view->isLogged = Auth::check();
        $this->view->user     = Auth::user();
        $this->view->menu     = Menu::fromRoutes($routes);
    }

    protected function render(string $template): string
    {
        $view = $this->view;

        ob_start();
        require __DIR__ . '/../Views/' . $template . '.php';
        return ob_get_clean();
    }
}
