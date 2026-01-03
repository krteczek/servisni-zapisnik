<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected ViewContext $view;

    public function __construct(ViewContext $view)
    {
        $this->view = $view;
    }

    protected function render(string $template): string
    {
        $view = $this->view;
			
        ob_start();
        require __DIR__ . '/../Views/' . $template . '.php';
        return ob_get_clean();
    }
}
