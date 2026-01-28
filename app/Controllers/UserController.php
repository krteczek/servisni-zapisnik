<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Core\Roles;
use App\Core\Flash;
use App\Core\UserGuard;
use App\Models\UserModel;

final class UserController extends Controller
{
    private UserModel $users;

    /* =============================
     * DB LIMITY
     * ============================= */

    private const MAX_EMAIL_LENGTH           = 255;
    private const MAX_EMPLOYEE_NUMBER_LENGTH = 50;
    private const MAX_FIRST_NAME_LENGTH      = 100;
    private const MAX_LAST_NAME_LENGTH       = 100;
    private const MAX_PASSWORD_LENGTH        = 255;

    public function __construct($view)
    {
        parent::__construct($view);
        $this->users = new UserModel();
    }

    /* =============================
     * LIST
     * ============================= */

    public function index(): string
    {
        $this->view->users = $this->users->all();
        return $this->render('users/index');
    }

    /* =============================
     * CREATE
     * ============================= */

    public function create(): string
    {
        $this->view->roles = Roles::effective();
        return $this->render('users/create');
    }

    public function store(): string
    {
        $data = $_POST;
        $this->view->data  = $data;
        $this->view->roles = Roles::effective();

        $this->checkCsrf();
        $this->validateUserData($data);

        if ($this->hasErrors()) {
            return $this->render('users/create');
        }

        $this->users->insert([
            'email'           => strtolower(trim($data['email'])),
            'employee_number' => trim($data['employee_number']),
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
            'password_hash'   => password_hash($data['new_password'], PASSWORD_DEFAULT),
            'global_role'     => $data['global_role'],
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        Flash::add(
            'success',
            'Uživatel ' . $data['first_name'] . ' ' . $data['last_name'] . ' byl úspěšně vytvořen.'
        );

        Url::redirect('/users');
    }

    /* =============================
     * EDIT
     * ============================= */

    public function edit(int $id): string
    {
        $user = $this->users->find($id);
        if (!$user) {
            Flash::add('error', 'Uživatel neexistuje.');
            Url::redirect('/users');
        }
    	if (UserGuard::isProtected($user)) {
			Flash::add('error','Tento účet nelze upravovat.');
			Url::redirect('/users');
		}

        $this->view->old   = $user;
        $this->view->roles = Roles::effective();

        return $this->render('users/edit');
    }

    public function update(int $id): string
    {
       $old = $this->users->find($id);
        if (!$old) {
            Flash::add('error', 'Uživatel neexistuje.');
            Url::redirect('/users');
        }
    	if (UserGuard::isProtected($old)) {
			Flash::add('error','Tento účet nelze upravovat.');
			Url::redirect('/users');
		}
 
        $data = $_POST;

        $this->view->data  = $data;
        $this->view->old   = $old;
        $this->view->roles = Roles::effective();

        $this->checkCsrf();
        $this->validateUserData($data, $old);

        if ($this->hasErrors()) {
            return $this->render('users/edit');
        }

        $update = [
            'email'           => strtolower(trim($data['email'])),
            'employee_number' => trim($data['employee_number']),
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
            'global_role'     => $data['global_role'],
            'active'          => isset($data['active']) ? 1 : 0,
        ];

        if ($this->users->update($id, $update)) {
            Flash::add(
                'success',
                'Data uživatele ' . $data['first_name'] . ' ' . $data['last_name'] . ' byla změněna.'
            );
        } else {
            Flash::add(
                'error',
                'Data uživatele se nepodařilo změnit.'
            );
        }

        Url::redirect('/users');
    }

    /* =============================
     * VALIDACE
     * ============================= */

    private function validateUserData(array $data, ?array $old = null): void
    {
        $email          = strtolower(trim($data['email'] ?? ''));
        $employeeNumber = trim($data['employee_number'] ?? '');
        $firstname      = trim($data['first_name'] ?? '');
        $lastname       = trim($data['last_name'] ?? '');

        $password1 = $data['new_password'] ?? '';
        $password2 = $data['new_password_confirm'] ?? '';

        /* ---- jméno ---- */

        if ($firstname === '') {
            $this->addError('first_name', 'Jméno je povinné.');
        } elseif (mb_strlen($firstname) > self::MAX_FIRST_NAME_LENGTH) {
            $this->addError('first_name', 'Jméno je příliš dlouhé.');
        }

        if ($lastname === '') {
            $this->addError('last_name', 'Příjmení je povinné.');
        } elseif (mb_strlen($lastname) > self::MAX_LAST_NAME_LENGTH) {
            $this->addError('last_name', 'Příjmení je příliš dlouhé.');
        }

        /* ---- email ---- */

        if ($email === '') {
            $this->addError('email', 'Email je povinný.');
        } elseif (mb_strlen($email) > self::MAX_EMAIL_LENGTH) {
            $this->addError('email', 'Email je příliš dlouhý.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Email nemá platný formát.');
        } elseif (
            !$old && $this->users->emailExists($email)
            || $old && $email !== $old['email'] && $this->users->emailExists($email, (int) $old['id'])
        ) {
            $this->addError('email', 'Email již existuje.');
        }

        /* ---- osobní číslo ---- */

        if ($employeeNumber === '') {
            $this->addError('employee_number', 'Osobní číslo je povinné.');
        } elseif (mb_strlen($employeeNumber) > self::MAX_EMPLOYEE_NUMBER_LENGTH) {
            $this->addError('employee_number', 'Osobní číslo je příliš dlouhé.');
        } elseif (
            !$old && $this->users->employeeNumberExists($employeeNumber)
            || $old && $employeeNumber !== $old['employee_number']
                && $this->users->employeeNumberExists($employeeNumber, (int) $old['id'])
        ) {
            $this->addError('employee_number', 'Osobní číslo již existuje.');
        }

        /* ---- role (bez root) ---- */

        $role = $data['global_role'] ?? Roles::default();

        if (!isset(Roles::effective()[$role])) {
            $this->addError('global_role', 'Neplatná role.');
        }

        /* ---- heslo ---- */

        if ($old === null || $password1 !== '') {

            if ($password1 === '') {
                $this->addError('new_password', 'Heslo je povinné.');
            } elseif (mb_strlen($password1) < 8) {
                $this->addError('new_password', 'Heslo musí mít alespoň 8 znaků.');
            } elseif (mb_strlen($password1) > self::MAX_PASSWORD_LENGTH) {
                $this->addError('new_password', 'Heslo je příliš dlouhé.');
            }

            if ($password1 !== $password2) {
                $this->addError('new_password', 'Hesla se neshodují.');
            }
        }
    }
    
    public static function isProtected(array $user): bool
{
    if ($user['global_role'] === 'root') {
        return true;
    }

    if (($user['domain_admin'] ?? 0) === 1) {
        return true;
    }

    return false;
}


    public function passwordForm(int $id): string
    {
        $user = $this->users->find($id);
        if (!$user) Url::redirect('/users');





        $this->view->data = $user;
        return $this->render('users/password');

    }

    public function updatePassword(int $id): string
    {
        $user = $this->users->find($id);
        if (!$user) Url::redirect('/users');



        $password = $_POST['new_password'] ?? '';
        $confirm  = $_POST['new_password_confirm'] ?? '';

        $this->checkCsrf();

        if ($password === '') $this->addError('password','Heslo je povinné');
        if ($password !== $confirm) $this->addError('password','Hesla se neshodují');
        if (mb_strlen($password) < 8) $this->addError('password','Heslo musí mít alespoň 8 znaků');





        if ($this->hasErrors()) {
            $this->view->data = $user;
            return $this->render('users/password');

        }

        $this->users->update($id,['password_hash'=>password_hash($password,PASSWORD_DEFAULT)]);
        Flash::add('success','Heslo uživatele '.$user['first_name'].' '.$user['last_name'].' bylo úspěšně změněno.');





        Url::redirect('/users');
    }


}