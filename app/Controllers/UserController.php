<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Url;
use App\Models\User;

class UserController extends Controller
{
    public function index(): void
    {
        $this->view->users = User::all();
        $this->render('users/index');
    }

    public function create(): void
    {
        $this->view->csrf = $this->csrfField();
        $this->view->data = [];

        $this->render('users/create');
    }

    public function store(): void
    {
        $data = $_POST;

        $this->checkCsrf();
        $this->validateUser($data, true);

        if ($this->hasErrors()) {
            $this->view->csrf = $this->csrfField();
            $this->view->data = $data;
            return $this->render('users/create');
        }

        User::create($data);

        Flash::add('success', 'Uživatel byl úspěšně vytvořen.');
        Url::redirect('/users');
    }

    public function edit(int $id): void
    {
        $user = User::find($id);

        if (!$user) {
            Flash::add('error', 'Uživatel nebyl nalezen.');
            Url::redirect('/users');
        }

        $this->view->csrf = $this->csrfField();
        $this->view->data = $user;

        $this->render('users/edit');
    }

    public function update(int $id): void
    {
        $data = $_POST;

        $this->checkCsrf();
        $this->validateUser($data, false);

        if ($this->hasErrors()) {
            $this->view->csrf = $this->csrfField();
            $this->view->data = array_merge($data, ['id' => $id]);
            return $this->render('users/edit');
        }

        User::update($id, $data);

        Flash::add('success', 'Uživatel byl úspěšně upraven.');
        Url::redirect('/users');
    }

    /**
     * Jediná validační funkce pro create i update
     */
    protected function validateUser(array $data, bool $requirePassword): void
    {
        if (empty($data['name'])) {
            $this->addError('name', 'Jméno je povinné.');
        }

        if (empty($data['email'])) {
            $this->addError('email', 'Email je povinný.');
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Email nemá platný formát.');
        }

        if ($requirePassword) {
            if (empty($data['password'])) {
                $this->addError('password', 'Heslo je povinné.');
            } elseif (strlen($data['password']) < 8) {
                $this->addError('password', 'Heslo musí mít alespoň 8 znaků.');
            }
        } else {
            if (!empty($data['password']) && strlen($data['password']) < 8) {
                $this->addError('password', 'Heslo musí mít alespoň 8 znaků.');
            }
        }

        if (empty($data['role'])) {
            $this->addError('role', 'Role je povinná.');
        }
    }
}