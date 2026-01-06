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

        redirect(Url::to('/users'));
    }
}
