<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Roles;
use App\Core\Url;
use App\Core\ViewContext;
use App\Core\Flash;
use App\Models\UserModel;

class UserController extends Controller
{
    private UserModel $model;

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->model = new UserModel();
    }

    /* =========================
       LIST
       ========================= */

    public function index(): string
    {
        $this->view->users = $this->model->all();
        return $this->render('users/index');
    }

    /* =========================
       CREATE
       ========================= */

    public function create(): string
    {
        $this->view->roles = Roles::all();
        $this->view->old   = $this->view->old ?? [];
        $this->view->selectedRole = $this->view->old['global_role'] ?? Roles::default();

        return $this->render('users/create');
    }

    public function store(): string
    {
        $data = [
            'email'           => trim($_POST['email'] ?? ''),
            'employee_number' => trim($_POST['employee_number'] ?? ''),
            'first_name'      => trim($_POST['first_name'] ?? ''),
            'last_name'       => trim($_POST['last_name'] ?? ''),
            'password'        => $_POST['new_password'] ?? '',
            'password_confirm'=> $_POST['new_password_confirm'] ?? '',
            'global_role'     => $_POST['global_role'] ?? Roles::default(),
        ];

        $this->view->old = $data;

        $this->validate($data, true);

        if ($this->model->existsByEmail($data['email'])) {
            $this->addError('email', 'Email už existuje');
        }

        if ($this->model->existsByEmployeeNumber($data['employee_number'])) {
            $this->addError('employee_number', 'Číslo zaměstnance už existuje');
        }

        if ($data['password'] !== $data['password_confirm']) {
            $this->addError('password', 'Hesla se neshodují');
        }

        if ($this->hasErrors()) {
            return $this->create();
        }

        $this->model->create([
            'email'           => $data['email'],
            'employee_number' => $data['employee_number'],
            'password_hash'   => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'global_role'     => $data['global_role'],
        ]);

        Flash::add('success', 'Uživatel byl vytvořen.');
        Url::redirect('/users');
    }

    /* =========================
       EDIT
       ========================= */

    public function editForm(int $id): string
    {
        $user = $this->model->findByIdFull($id);
        if (!$user) {
            return $this->forbidden();
        }

        $this->view->old   = $user;
        $this->view->roles = Roles::all();

        return $this->render('users/edit');
    }

    public function edit(int $id): string
    {
        $user = $this->model->findByIdFull($id);
        if (!$user) {
            return $this->forbidden();
        }

        $data = [
            'email'           => trim($_POST['email'] ?? ''),
            'employee_number' => trim($_POST['employee_number'] ?? ''),
            'first_name'      => trim($_POST['first_name'] ?? ''),
            'last_name'       => trim($_POST['last_name'] ?? ''),
            'global_role'     => $_POST['global_role'] ?? Roles::default(),
            'active'          => isset($_POST['active']) ? 1 : 0,
        ];

        $this->view->old = array_merge($user, $data);

        $this->validate($data, false);

        if ($this->model->existsByEmail($data['email'], $id)) {
            $this->addError('email', 'Email už existuje');
        }

        if ($this->model->existsByEmployeeNumber($data['employee_number'], $id)) {
            $this->addError('employee_number', 'Číslo zaměstnance už existuje');
        }

        if ($this->hasErrors()) {
            return $this->render('users/edit');
        }

        $this->model->update($id, $data);

        Flash::add('success', 'Uživatel byl upraven.');
        Url::redirect('/users');
    }

    /* =========================
       PASSWORD
       ========================= */

    public function passwordForm(int $id): string
    {
        $user = $this->model->findById($id);
        if (!$user) {
            Url::redirect('/users');
        }

        $this->view->data = $user;
        return $this->render('users/password');
    }

    public function updatePassword(int $id): string
    {
        $user = $this->model->findById($id);
        if (!$user) {
            Url::redirect('/users');
        }

        $password = $_POST['new_password'] ?? '';
        $confirm  = $_POST['new_password_confirm'] ?? '';

        $this->checkCsrf();

        if ($password === '') {
            $this->addError('password', 'Heslo je povinné');
        } elseif (mb_strlen($password) < 8) {
            $this->addError('password', 'Heslo musí mít alespoň 8 znaků');
        } elseif ($password !== $confirm) {
            $this->addError('password', 'Hesla se neshodují');
        }

        if ($this->hasErrors()) {
            $this->view->data = $user;
            return $this->render('users/password');
        }

        $this->model->updatePassword(
            $id,
            password_hash($password, PASSWORD_DEFAULT)
        );

        Flash::add('success', 'Heslo bylo změněno.');
        Url::redirect('/users');
    }

    /* =========================
       VALIDATION
       ========================= */

    private function validate(array $data, bool $requirePassword): void
    {
        $this->checkCsrf();

        if ($data['email'] === '') {
            $this->addError('email', 'Email je povinný');
        } elseif (!$this->isValidEmail($data['email'])) {
            $this->addError('email', 'Email nemá podporovaný tvar');
        }

        if ($requirePassword) {
            if ($data['password'] === '') {
                $this->addError('password', 'Heslo je povinné');
            } elseif (mb_strlen($data['password']) < 8) {
                $this->addError('password', 'Heslo musí mít alespoň 8 znaků');
            }
        }

        if ($data['employee_number'] === '') {
            $this->addError('employee_number', 'Číslo zaměstnance je povinné');
        } elseif (mb_strlen($data['employee_number']) > 50) {
            $this->addError('employee_number', 'Číslo zaměstnance je příliš dlouhé');
        }

        if (!Roles::exists($data['global_role'])) {
            $this->addError('global_role', 'Neplatná role');
        }
    }

    private function isValidEmail(string $email): bool
    {
        return mb_strlen($email) <= 254
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}