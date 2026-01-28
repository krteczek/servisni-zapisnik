<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\AccessLogger;

abstract class Controller
{
    protected ViewContext $view;

    public function __construct(ViewContext $view)
    {
        $this->view = $view;
        $this->view->errors ??= [];
        $this->view->data   ??= [];
			if (Auth::check()) {
            $db = Session::get('user.db_name');
            if ($db) {
                Database::useWorkDatabase($db);
            }
        }
	}

    protected function render(string $template): string
    {
        $view = $this->view;

        ob_start();
        require __DIR__ . '/../Views/' . $template . '.php';
        return ob_get_clean();
    }

    /* =========================
       CSRF
       ========================= */

    protected function csrfField(): string
    {
        return Csrf::getField();
    }

    protected function checkCsrf(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? '')) {
            $this->addError('_csrf', 'Platnost formuláře vypršela. Zkuste jej odeslat znovu.');
        }
    }

    /* =========================
       ERRORS
       ========================= */

    protected function addError(string $field, string $message): void
    {
        $this->view->errors[$field][] = $message;
    }

    protected function hasErrors(): bool
    {
        return !empty($this->view->errors);
    }

    /* =========================
       COMMON PAGES
       ========================= */

    public function forbidden(): string
    {
    		AccessLogger::log('403');
        http_response_code(403);
        $this->view->title = '403 – Přístup zakázán';
        return $this->render('errors/403');
    }

    public function notFound(): string
    {
    	AccessLogger::log('404');
        http_response_code(404);
        $this->view->title = '404 – Stránka nenalezena';
        return $this->render('errors/404');
    }
}