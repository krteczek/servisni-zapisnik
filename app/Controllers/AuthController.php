<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\UserModel;

class AuthController extends Controller
{
    public function root(): string
    {
        redirect(Auth::check() ? '/dashboard' : '/login');
    }

    public function loginForm(): string
    {
        $this->view->csrf   = Csrf::token();
        $this->view->errors = [];
        $this->view->data   = [];

        return $this->render('auth/login');
    }

    public function login(): string
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $this->view->csrf = Csrf::token();
        $this->view->errors = [];
        $this->view->data = [
            'email' => $email,
        ];

        if ($email === '') {
            $this->view->errors['email'][] = 'Email je povinný';
        }

        if ($password === '') {
            $this->view->errors['password'][] = 'Heslo je povinné';
        }

        if ($this->view->errors) {
            return $this->render('auth/login');
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->view->errors['global'][] = 'Neplatné přihlašovací údaje';
            return $this->render('auth/login');
        }
    \App\Core\Session::regenerate();
        $_SESSION['user'] = [
            'id'          => (int) $user['id'],
            'email'       => $user['email'],
            'global_role' => $user['global_role'],
            'first_name'  => $user['first_name'] ?? null,
            'last_name'   => $user['last_name'] ?? null,
        ];
			
        $_SESSION['permissions'] = $this->loadPermissions((int) $user['id']);

        redirect('/dashboard');
    }

    public function logout(): string
    {
        Auth::logout();
			\App\Core\Session::destroy();

        redirect('/login');
    }

    private function loadPermissions(int $userId): array
    {
        $pdo = Database::pdo();

        $stmt = $pdo->prepare(
            'SELECT permission_code
             FROM user_permissions
             WHERE user_id = ?'
        );
        $stmt->execute([$userId]);

        return array_column($stmt->fetchAll(), 'permission_code');
    }
}
