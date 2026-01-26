<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Url;
use App\Models\UserModel;
use App\Models\CompanyModel;

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

    Auth::login([
        'id'          => (int) $user['id'],
        'email'       => $user['email'],
        'global_role' => $user['global_role'],
        'company_id'  => (int) $company['id'],
        'company'     => $company['slug'],
        'first_name'  => $user['first_name'] ?? null,
        'last_name'   => $user['last_name'] ?? null,
    ]);

    Flash::add(
        'success',
        'Vítej v aplikaci, ' . ($user['first_name'] ?? $user['email']) . ' 👋'
    );

    Url::redirect('/dashboard');
}
    
/** starý login, jen pro případ...
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


        /* ===== VALIDACE DAT ===== * /

			if ($tenant === '') {
			    $this->addError('tenant', 'Firma je povinná');
			}

        if ($email === '') {
            $this->addError('email', 'Email je povinný');
        }

        if ($password === '') {
            $this->addError('password', 'Heslo je povinné');
        }

        /* ===== CSRF (součást validace) ===== * /

        $this->checkCsrf();

        /* ===== FINÁLNÍ KONTROLA ===== * /

        if ($this->hasErrors()) {
            return $this->render('auth/login');
        }

        /* ===== AUTH ===== * /

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->addError('global', 'Neplatný email nebo heslo.');
            return $this->render('auth/login');
        }

Auth::login([
    'id'           => (int) $user['id'],
    'email'        => $user['email'],
    'global_role'  => $user['global_role'],
    'company_id'   => (int) $company['id'],
    'company_slug' => $company['slug'],
    'first_name'   => $user['first_name'] ?? null,
    'last_name'    => $user['last_name'] ?? null,
]);

        Flash::add(
            'success',
            'Vítej v aplikaci, ' . ($user['first_name'] ?? $user['email']) . ' 👋'
        );

        Url::redirect('/');
    }
**/

    public function logout(): string
    {
        Flash::add('success', 'Byl jste odhlášen. Přijďte zas!');

        Auth::logout();
        Url::redirect('/login');
    }
}