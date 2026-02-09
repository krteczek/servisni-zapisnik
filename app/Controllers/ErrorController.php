<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Throwable;

final class ErrorController extends Controller
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

    public function serverError(Throwable $e): string
    {
        http_response_code(500);
        $this->view->title = '500 – Interní chyba serveru';

        if (defined('APP_DEBUG') && APP_DEBUG) {
            $this->view->exception = $e;
        }

        return $this->render('errors/500');
    }
    
    public function renderError(int $code, ?Throwable $e = null): string
    {
        http_response_code($code);

        $this->view->errorCode = $code;

        switch ($code) {
            case 403:
                $this->view->title = '403 – Přístup zakázán';
                return $this->render('errors/403');

            case 404:
                $this->view->title = '404 – Stránka nenalezena';
                return $this->render('errors/404');

            default:
                $this->view->title = '500 – Chyba aplikace';
                if (APP_ENV === 'dev' && $e) {
                    $this->view->exception = $e;
                }
                return $this->render('errors/500');
        }
    }
}
