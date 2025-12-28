<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;

class AuthController extends Controller
{
    public function loginForm(): string
    {
        return $this->view('auth/login', [
            'csrf' => Csrf::token()
        ]);
    }

    public function login(): string
    {
        if (!Csrf::check($_POST['_csrf'] ?? '')) {
            return 'Neplatný CSRF token';
        }

        // ZATÍM NAPEČNO
        if ($_POST['login'] === 'admin' && $_POST['password'] === 'admin') {
            $_SESSION['user'] = [
                'id' => 1,
                'role' => 'admin'
            ];
            header('Location: /');
            exit;
        }

        return 'Neplatné přihlašovací údaje';
    }
}