<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Core\Roles;
use App\Core\Flash;
use App\Core\Auth;
use App\Models\UserModel;

final class UserController extends Controller
{
    private UserModel $users;

    public function __construct($view)
    {
        parent::__construct($view);
        $this->users = new UserModel();
    }

    public function index(): string
    {
        $this->view->users = $this->users->all();
        return $this->render('users/index');
    }

    public function create(): string
    {
        $this->view->roles = Roles::effective();
        return $this->render('users/create');
    }

    public function store(): string
    {
        $this->view->roles = Roles::effective();
        $data = $_POST;
        $this->view->data = $data;

        $email = strtolower(trim($data['email'] ?? ''));
        $employeeNumber = trim($data['employee_number'] ?? '');
        $firstname = trim($data['first_name'] ?? '');
        $lastname = trim($data['last_name'] ?? '');
        $password1 = $data['new_password'] ?? '';
        $password2 = $data['new_password_confirm'] ?? '';

        // VALIDACE
        if ($firstname === '') $this->addError('first_name','Jméno je povinné.');
        if ($lastname === '') $this->addError('last_name','Příjmení je povinné.');
        if ($email === '') $this->addError('email','Email je povinný.');
        if ($employeeNumber === '') $this->addError('employee_number','Osobní číslo je povinné.');
        if ($password1 === '') $this->addError('new_password','Heslo je povinné.');
        if ($password1 !== $password2) $this->addError('new_password','Hesla nejsou shodná.');

        // CSRF
        $this->checkCsrf();
        if ($this->hasErrors()) return $this->render('users/create');

        // ROLE
        $role = $data['global_role'] ?? Roles::default();
        if ($role === 'root' && !Auth::hasGlobalRole(['root'])) $role = Roles::default();

        $this->users->insert([
            'email'           => $email,
            'employee_number' => $employeeNumber,
            'first_name'      => $firstname,
            'last_name'       => $lastname,
            'password_hash'   => password_hash($password1, PASSWORD_DEFAULT),
            'global_role'     => $role,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        Flash::add('success','Uživatel '.$firstname.' '.$lastname.' byl úspěšně vytvořen.');
        Url::redirect('/users');
    }

    public function edit(int $id): string
    {
        $old = $this->users->find($id);
        if (!$old) {
            Flash::add('error','Uživatel neexistuje');
            Url::redirect('/users');
        }
        $this->view->old = $old;
        $this->view->roles = Roles::effective();
        return $this->render('users/edit');
    }

    public function update(int $id): string
    {
        $old = $this->users->find($id);
        if (!$old) {
            Flash::add('error','Uživatel neexistuje');
            Url::redirect('/users');
        }

        $data = $_POST;
        $this->view->data = $data;
        $this->view->old = $old;

        $email = strtolower(trim($data['email'] ?? ''));
        $employeeNumber = trim($data['employee_number'] ?? '');
        $firstname = trim($data['first_name'] ?? '');
        $lastname = trim($data['last_name'] ?? '');
        $active = isset($data['active']) ? 1 : 0;

        // VALIDACE
        if ($firstname === '') $this->addError('first_name','Jméno je povinné.');
        if ($lastname === '') $this->addError('last_name','Příjmení je povinné.');
        if ($email === '') $this->addError('email','Email je povinný.');
        if ($email !== $old['email'] && $this->users->emailExists($email,$id))
            $this->addError('email','Email již existuje.');
        if ($employeeNumber === '') $this->addError('employee_number','Osobní číslo je povinné.');
        if ($employeeNumber !== $old['employee_number'] && $this->users->employeeNumberExists($employeeNumber,$id))
            $this->addError('employee_number','Osobní číslo již existuje.');

        // ROLE
        $role = $data['global_role'] ?? Roles::default();
        if ($role === 'root' && !Auth::hasGlobalRole(['root'])) $role = Roles::default();

        $this->checkCsrf();
        if ($this->hasErrors()) return $this->render('users/edit');

        // UPDATE
        $update = [
            'email'           => $email,
            'employee_number' => $employeeNumber,
            'first_name'      => $firstname,
            'last_name'       => $lastname,
            'global_role'     => $role,
            'active'          => $active,
        ];

        if($this->users->update($id,$update)) {
            Flash::add('success','Data uživatele '.$firstname.' '.$lastname.' byla úspěšně změněna.');
        } else {
            Flash::add('error','Data uživatele '.$firstname.' '.$lastname.' se nepodařilo změnit.');
        }

        Url::redirect('/users');
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
