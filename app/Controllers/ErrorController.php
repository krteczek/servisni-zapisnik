<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
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
}
