<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Core\Roles;
use App\Core\Flash;
use App\Models\UserModel;

final class UserController extends Controller
{
    private UserModel $users;

    public function __construct($view)
    {
        parent::__construct($view);
        $this->users = new UserModel();
    }

    /* ==========================================================
     * LIST
     * ========================================================== */

    public function index(): string
    {
        $this->view->users = $this->users->all();
        return $this->render('users/index');
    }

    /* ==========================================================
     * CREATE
     * ========================================================== */

    public function create(): string
    {
        $this->view->csrf = $this->csrfField();
        $this->view->roles = Roles::all();
        return $this->render('users/create');
    }

public function store(): string
{
    $this->view->roles = Roles::all();

    $data = $_POST;

    $this->view->csrf = $this->csrfField();
    $this->view->data = $data;

    /* =========================
       NORMALIZACE VSTUPŮ
       ========================= */

    $email = strtolower(trim($data['email'] ?? ''));
    $employeeNumber = trim($data['employee_number'] ?? '');
    $firstname = trim($data['first_name'] ?? '');
    $lastname = trim($data['last_name'] ?? '');
    $password1 = $data['new_password'] ?? '';
    $password2 = $data['new_password_confirm'] ?? '';
    

    /* =========================
       VALIDACE
       ========================= */

    if ($firstname === '') {
        $this->addError('first_name', 'Jméno je povinné.');
    } elseif (mb_strlen($firstname) > 100) {
        $this->addError('first_name', 'Jméno je příliš dlouhé.');
    }
    if ($lastname === '') {
        $this->addError('last_name', 'Příjmení je povinné.');
    } elseif (mb_strlen($lastname) > 100) {
        $this->addError('last_name', 'Příjmení je příliš dlouhé.');
    }

    if ($email === '') {
        $this->addError('email', 'Email je povinný.');
    } elseif (mb_strlen($email) > 255) {
        $this->addError('email', 'Email je příliš dlouhý.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->addError('email', 'Email nemá platný tvar.');
    } elseif ($this->users->emailExists($email)) {
        $this->addError('email', 'Email již existuje.');
    }

    if ($employeeNumber === '') {
        $this->addError('employee_number', 'Osobní číslo je povinné.');
    } elseif (mb_strlen($employeeNumber) > 50) {
        $this->addError('employee_number', 'Osobní číslo je příliš dlouhé.');
    } elseif ($this->users->employeeNumberExists($employeeNumber)) {
        $this->addError('employee_number', 'Osobní číslo již existuje.');
    }

    if ($password1 === '') {
        $this->addError('new_password', 'Heslo je povinné');
    } elseif (mb_strlen($password1) < 8) {
        $this->addError('new_password', 'Heslo musí mít alespoň 8 znaků.');
    } 
    if ($password1 !== $password2) {
         $this->addError('new_password', 'Hesla nejsou shodná. Věnujte, prosím, jejich zápisu více pozornosti.');
   	
    }
    

    /* =========================
       CSRF
       ========================= */

    $this->checkCsrf();

    if ($this->hasErrors()) {
        return $this->render('users/create');
    }

    /* =========================
       ROLE (normalizace)
       ========================= */

    $role = $data['role'] ?? null;
    if (!$role || !Roles::exists($role)) {
        $role = Roles::default();
    }

    /* =========================
       INSERT
       ========================= */

    $this->users->insert([
        'email'           => $email,
        'employee_number' => $employeeNumber,
        'first_name'       => $firstname,
        'last_name'       => $lastname,
        'password_hash'   => password_hash($password1, PASSWORD_DEFAULT),
        'global_role'     => $role,
        'created_at'      => date('Y-m-d H:i:s'),
    ]);

    Flash::add('success', 'Uživatel byl vytvořen');
    Url::redirect('/users');
}

    /* ==========================================================
     * EDIT
     * ========================================================== */

    public function edit(int $id): string
    {
        $user = $this->users->find($id);

        if (!$user) {
            Flash::add('error', 'Uživatel neexistuje');
            Url::redirect('/users');
        }

        $this->view->user = $user;
        $this->view->csrf = $this->csrfField();

        return $this->render('users/edit');
    }

    public function update(int $id): string
    {
        $user = $this->users->find($id);

        if (!$user) {
            Flash::add('error', 'Uživatel neexistuje');
            Url::redirect('/users');
        }

        $data = $_POST;

        $this->view->csrf = $this->csrfField();
        $this->view->data = $data;

        /* ===== VALIDACE ===== */

        if (empty($data['name'])) {
            $this->addError('name', 'Jméno je povinné');
        }

        if (
            ($data['email'] ?? '') !== $user['email']
            && $this->users->emailExists($data['email'] ?? '')
        ) {
            $this->addError('email', 'Email již existuje');
        }

        if (
            ($data['employee_number'] ?? '') !== $user['employee_number']
            && $this->users->employeeNumberExists($data['employee_number'] ?? '')
        ) {
            $this->addError('employee_number', 'Osobní číslo již existuje');
        }

        $this->checkCsrf();

        if ($this->hasErrors()) {
            return $this->render('users/edit');
        }

        /* ===== UPDATE ===== */

        $update = [
            'name'            => trim($data['name']),
            'email'           => strtolower(trim($data['email'])),
            'employee_number' => trim($data['employee_number']),
            'global_role'     => $data['role'],
        ];

        if (!empty($data['password'])) {
            $update['password_hash'] =
                password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $this->users->update($id, $update);

        Flash::add('success', 'Uživatel byl uložen');
        Url::redirect('/users');
    }
}
