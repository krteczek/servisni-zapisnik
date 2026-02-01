<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Url;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Services\AuthTokenService;

use App\Core\Flash;

class AuthController extends Controller
{
    public function root(): string
    {
        Url::redirect(Auth::check() ? '/dashboard' : '/login');
    }

    public function loginForm(): string
    {
			if (Auth::check()) {
				Url::redirect('/dashboard');
			} 

        $this->view->csrf   = $this->csrfField();
        $this->view->errors = [];
        $this->view->data   = [];

        return $this->render('auth/login');
    }

public function login(): string
{
    $tenant   = trim($_POST['tenant'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $this->view->csrf = $this->csrfField();
    $this->view->data = [
        'tenant' => $tenant,
        'email'  => $email,
    ];

    /* ===== VALIDACE DAT ===== */

    if ($tenant === '') {
        $this->addError('tenant', 'Firma je povinná');
    }

    if ($email === '') {
        $this->addError('email', 'Email je povinný');
    }

    if ($password === '') {
        $this->addError('password', 'Heslo je povinné');
    }

    /* ===== CSRF ===== */

    $this->checkCsrf();

    if ($this->hasErrors()) {
        return $this->render('auth/login');
    }

    /* ===== AUTH ===== */

    $companyModel = new \App\Models\CompanyModel();
    $company = $companyModel->findBySlug($tenant);

    $user = null;

    if ($company) {
        $userModel = new UserModel();
        $user = $userModel->findByEmailAndCompany(
            $email,
            (int) $company['id']
        );
    }

    if (
        !$user ||
        !password_verify($password, $user['password_hash'])
    ) {
        $this->addError('global', 'Neplatné přihlašovací údaje.');
        return $this->render('auth/login');
    }

    /* ===== LOGIN OK ===== */
//Database::useWorkDatabase($company['db_name']);
    Auth::login([
        'id'          => (int) $user['id'],
        'email'       => $user['email'],
        'global_role' => $user['global_role'],
        'company_id'  => (int) $company['id'],
        'company'     => $company['slug'],
        'first_name'  => $user['first_name'] ?? null,
        'last_name'   => $user['last_name'] ?? null,
        'db_name'		 => $company['db_name'],
    ]);

    Flash::add(
        'success',
        'Vítej v aplikaci, ' . ($user['first_name'] ?? $user['email']) . ' 👋'
    );
Url::redirect(
    $user['global_role'] === 'root'
        ? '/system'
        : '/dashboard'
);

}
    

    public function logout(): string
    {
        Flash::add('success', 'Byl jste odhlášen. Přijďte zas!');

        Auth::logout();
        Url::redirect('/login');
    }
    


    /* =========================
     *  PUBLIC ROUTES
     * ========================= */

    public function activate(): string
    {
        return $this->handleTokenGet('activate');
    }

    public function activatePost(): string
    {
        return $this->handleTokenPost(
            'activate',
            function (int $userId, string $password): void {
                (new UserModel())->activateUser(
                    $userId,
                    password_hash($password, PASSWORD_DEFAULT)
                );
            },
            'Účet byl aktivován. Můžeš se přihlásit.'
        );
    }

    public function resetPassword(): string
    {
        return $this->handleTokenGet('reset_password');
    }

    public function resetPasswordPost(): string
    {
        return $this->handleTokenPost(
            'reset_password',
            function (int $userId, string $password): void {
                (new UserModel())->setPassword(
                    $userId,
                    password_hash($password, PASSWORD_DEFAULT)
                );
            },
            'Heslo bylo změněno.'
        );
    }

    /* =========================
     *  PRIVATE HELPERS
     * ========================= */

    private function handleTokenGet(string $type): string
    {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            Flash::error('Chybí token.');
            Url::redirect('/login');
        }

        try {
            (new AuthTokenService())->validate($token, $type);

            // žádný view – frontend/formulář řešíš jinde
            return 'OK';

        } catch (\Throwable) {
            Flash::error('Odkaz je neplatný nebo expirovaný.');
            Url::redirect('/login');
        }
    }

    private function handleTokenPost(
        string $type,
        callable $userAction,
        string $successMessage
    ): string {
        $token    = $_POST['token'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$token || !$password) {
            Flash::error('Neplatná data.');
            Url::redirect('/login');
        }

        try {
            $service = new AuthTokenService();
            $row = $service->consume($token, $type);

            $userAction((int) $row['user_id'], $password);

            Flash::success($successMessage);
            Url::redirect('/login');

        } catch (\Throwable $e) {
            error_log($e);
            Flash::error('Operace se nezdařila.');
            Url::redirect('/login');
        }
    }
}

}