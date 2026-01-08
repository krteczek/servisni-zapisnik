<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Core\Csrf;
use App\Core\Url;
use App\Core\Roles;

class UserController extends Controller
{
    public function index(): string
    {
        $model = new UserModel();
        $this->view->users = $model->all();
        //var_dump($this->view->users);

        return $this->render('users/index');
    }

    public function create(): string
    {
        $this->view->roles = Roles::all();
        $this->view->old   = $this->view->old ?? [];
        $this->view->selectedRole =
            $this->view->old['role'] ?? Roles::default();

        return $this->render('users/create');
    }

    public function store(): string
    {
        $data   = $_POST;
        $errors = [];

        /* ===== CSRF ===== */
        if (!Csrf::check($data['_token'] ?? '')) {
            $errors['_csrf'] = 'Neplatný CSRF token';
        }

        /* ===== VALIDACE ===== */
        if (empty($data['email'])) {
            $errors['email'] = 'Email je povinný';
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Jméno je povinné';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Příjmení je povinné';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Heslo je povinné';
        }

        if (empty($data['employee_number'])) {
            $errors['employee_number'] = 'Číslo zaměstnance je povinné';
        }

        if (!Roles::exists($data['role'] ?? '')) {
            $errors['role'] = 'Neplatná role';
        }

        /* ===== PŘI CHYBÁCH ===== */
        if ($errors) {
            $this->view->errors = $errors;
            $this->view->old    = $data;
            return $this->create();
        }

        $model = new UserModel();

        if ($model->existsByEmail($data['email'])) {
            $this->view->errors = ['email' => 'Email už existuje'];
            $this->view->old    = $data;
            return $this->create();
        }

        /* ===== CREATE ===== */
        $model->create([
            'email'            => $data['email'],
            'password_hash'    => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'employee_number'  => $data['employee_number'],
            'global_role'      => $data['role'],
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
			Url::redirect('/users');
    }
    
public function editForm(): string
{
    $id = (int) ($_GET['id'] ?? 0);

    $user = (new UserModel())->findByIdFull($id);
    if (!$user) {
        return $this->forbidden();
    }

    $this->view->old = $user;
    $this->view->roles = \App\Core\Roles::all();

    return $this->render('users/edit');
}

public function edit(): string
{
    $id = (int) ($_GET['id'] ?? 0);
    $model = new UserModel();

    $data = [
        'email'           => trim($_POST['email'] ?? ''),
        'employee_number' => trim($_POST['employee_number'] ?? ''),
        'first_name'      => trim($_POST['first_name'] ?? ''),
        'last_name'       => trim($_POST['last_name'] ?? ''),
        'global_role'     => $_POST['global_role'] ?? 'monter',
        'active'          => isset($_POST['active']) ? 1 : 0,
    ];

    /* === VALIDACE === */
    $errors = [];

    if ($data['email'] === '') {
        $errors['email'][] = 'Email je povinný';
    }

    if (!\App\Core\Roles::exists($data['global_role'])) {
        $errors['global_role'][] = 'Neplatná role';
    }

    if ($errors) {
        $this->view->errors = $errors;
        $this->view->userData = array_merge($model->findByIdFull($id), $data);
        return $this->render('users/edit');
    }

    $model->update($id, $data);

    \App\Core\Session::flash('message', [
        'type' => 'success',
        'text' => 'Uživatel byl upraven',
    ]);

    \App\Core\Url::redirect('/users');
}

}
