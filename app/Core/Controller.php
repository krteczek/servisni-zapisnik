<?php
declare(strict_types=1);

namespace App\Core;
use \app\Core\ViewContext;

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
    
    public function forbidden(): string
    {
        http_response_code(403);

        $this->view->title = '403 – Přístup zakázán';

        return $this->render('errors/403');
    }

    public function notFound(): string
    {
        http_response_code(404);

        $this->view->title = '404 – Stránka nenalezena';

        return $this->render('errors/404');
    }

public function switchRole(string $role): void
{
    $allowed = array_keys(Config::get('roles')['roles']);

    if (!in_array($role, $allowed, true)) {
        throw new DomainException('Neplatná role');
    }
	Session::set('effective_role',$role);
    //$_SESSION['effective_role'] = $role;

    Url::redirect('/');
}

}