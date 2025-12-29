<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;

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

        $email = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            return 'Vyplň přihlašovací údaje';
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, role_id, password_hash 
             FROM users 
             WHERE email = :email 
               AND terminated_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return 'Neplatný e-mail nebo heslo';
        }

        // login OK
        $_SESSION['user'] = [
            'id'   => (int)$user['id'],
            'role' => $user['role'],
        ];

        header('Location: /dashboard');
        exit;
    }


public function logout(): void
{
    Auth::logout();
}
public function index(): never
{
	
    if (!Auth::check()) {
    	
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }

    header('Location: ' . BASE_PATH . '/dashboard');
    exit;
}

public function root(): string
{
    if (\App\Core\Auth::check()) {
        header('Location: ' . BASE_PATH . '/dashboard');
        exit;
    }

    header('Location: ' . BASE_PATH . '/login');
    exit;
}


}