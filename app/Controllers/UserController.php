<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Core\Csrf;

class UserController extends Controller
{
    public function index(): string
    {
        $model = new UserModel();
        $users = $model->all();

        $this->view->data['users'] = $users;

        return $this->render('users/index');
    }

    public function create(): string
    {
        return $this->render('users/create');
    }

    public function store(): string
    {
        if (!isset($_POST['_csrf']) || !Csrf::check($_POST['_csrf'])) {
            $this->view->errors = ['Neplatný CSRF token'];
            return $this->render('users/create');
        }

        $data = $_POST;
        $errors = [];

        if (empty($data['email'])) {
            $errors[] = 'Email je povinný';
        }

        if (empty($data['password'])) {
            $errors[] = 'Heslo je povinné';
        }

        if ($errors) {
            $this->view->errors = $errors;
            return $this->render('users/create');
        }

        $model = new UserModel();

        if ($model->existsByEmail($data['email'])) {
            $this->view->errors = ['Email už existuje'];
            return $this->render('users/create');
        }

        $model->create([
            'email'            => $data['email'],
            'password_hash'    => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name'       => $data['first_name'] ?? '',
            'last_name'        => $data['last_name'] ?? '',
            'employment_start' => date('Y-m-d'),
            'role_id'          => (int) $data['role_id'],
            'hired_at'         => date('Y-m-d'),
        ]);

        redirect('/users');
        return '';
    }
}
