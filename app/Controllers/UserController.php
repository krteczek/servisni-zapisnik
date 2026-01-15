<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class UserController extends Controller
{
    private UserModel $users;

    public function __construct(\App\Core\ViewContext $view)
    {
        parent::__construct($view);
        $this->users = new UserModel();
    }

    /* =========================
       LIST
       ========================= */

    public function index(): string
    {
        $this->view->title = 'Uživatelé';
        $this->view->users = $this->users->all();

        return $this->render('users/index');
    }

    /* =========================
       CREATE
       ========================= */

    public function create(): string
    {
        $this->view->title = 'Nový uživatel';
        return $this->render('users/create');
    }

    public function store(): string
    {
        $this->checkCsrf();

        $data = $this->sanitize($_POST);

        $this->validate($data, isNew: true);

        if ($this->hasErrors()) {
            $this->view->data = $data;
            return $this->render('users/create');
        }

        $this->users->create([
            'email'           => $data['email'],
            'employee_number' => $data['employee_number'],
            'password_hash'   => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'global_role'     => $data['global_role'],
            'active'          => (int)$data['active'],
        ]);

        header('Location: /users');
        exit;
    }

    /* =========================
       EDIT
       ========================= */

    public function edit(int $id): string
    {
        $user = $this->users->find($id);

        if (!$user) {
            return $this->notFound();
        }

        $this->view->title = 'Upravit uživatele';
        $this->view->data  = $user;

        return $this->render('users/edit');
    }

    public function update(int $id): string
    {
        $user = $this->users->find($id);

        if (!$user) {
            return $this->notFound();
        }

        $this->checkCsrf();

        $data = $this->sanitize($_POST);

        $this->validate($data, isNew: false, userId: $id);

        if ($this->hasErrors()) {
            $this->view->data = array_merge($user, $data);
            return $this->render('users/edit');
        }

        $update = [
            'email'           => $data['email'],
            'employee_number' => $data['employee_number'],
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'global_role'     => $data['global_role'],
            'active'          => (int)$data['active'],
        ];

        if (!empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $this->users->update($id, $update);

        header('Location: /users');
        exit;
    }

    /* =========================
       VALIDATION
       ========================= */

    private function validate(array $data, bool $isNew, ?int $userId = null): void
    {
        // EMAIL
        if ($data['email'] === '') {
            $this->addError('email', 'Email je povinný');
        } elseif (strlen($data['email']) > 255) {
            $this->addError('email', 'Email je příliš dlouhý');
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Neplatný formát emailu');
        } elseif ($this->users->emailExists($data['email'], $userId)) {
            $this->addError('email', 'Email už existuje');
        }

        // EMPLOYEE NUMBER
        if ($data['employee_number'] === '') {
            $this->addError('employee_number', 'Osobní číslo je povinné');
        } elseif (strlen($data['employee_number']) > 50) {
            $this->addError('employee_number', 'Osobní číslo je příliš dlouhé');
        } elseif ($this->users->employeeNumberExists($data['employee_number'], $userId)) {
            $this->addError('employee_number', 'Osobní číslo už existuje');
        }

        // PASSWORD
        if ($isNew && $data['password'] === '') {
            $this->addError('password', 'Heslo je povinné');
        }

        if ($data['password'] !== '' && strlen($data['password']) < 8) {
            $this->addError('password', 'Heslo musí mít alespoň 8 znaků');
        }

        // FIRST / LAST NAME
        if (strlen($data['first_name']) > 100) {
            $this->addError('first_name', 'Jméno je příliš dlouhé');
        }

        if (strlen($data['last_name']) > 100) {
            $this->addError('last_name', 'Příjmení je příliš dlouhé');
        }

        // ROLE
        if (!in_array($data['global_role'], ['admin', 'mistr', 'predak', 'monter'], true)) {
            $this->addError('global_role', 'Neplatná role');
        }
    }

    /* =========================
       SANITIZE
       ========================= */

    private function sanitize(array $input): array
    {
        return [
            'email'           => trim($input['email'] ?? ''),
            'employee_number' => trim($input['employee_number'] ?? ''),
            'password'        => $input['password'] ?? '',
            'first_name'      => trim($input['first_name'] ?? ''),
            'last_name'       => trim($input['last_name'] ?? ''),
            'global_role'     => $input['global_role'] ?? 'monter',
            'active'          => isset($input['active']) ? 1 : 0,
        ];
    }
}
