<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Url;
use App\Models\UserModel;

/**
 * Správa uživatelů (CRUD)
 */
class UserController extends Controller
{
    /**
     * Přehled uživatelů
     */
    public function index(): string
    {
        $this->view->users = (new UserModel())->all();
        return $this->render('users/index');
    }

    /**
     * Formulář pro vytvoření uživatele
     */
    public function create(): string
    {
        $this->view->roles = Roles::all();
        $this->view->old   = $this->view->old ?? [];
        $this->view->selectedRole = $this->view->old['global_role'] ?? Roles::default();

        return $this->render('users/create');
    }

    /**
     * Uložení nového uživatele
     */
    public function store(): string
    {
        $data   = $_POST;
        $errors = $this->validate($data, true);

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

        if ($model->existsByEmployeeNumber($data['employee_number'])) {
            $this->view->errors = ['employee_number' => 'Číslo zaměstnance už existuje'];
            $this->view->old    = $data;
            return $this->create();
        }

        $model->create([
            'email'            => $data['email'],
            'employee_number'  => $data['employee_number'],
            'password_hash'    => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'global_role'      => $data['global_role'],
        ]);

        Url::redirect('/users');
    }

    /**
     * Formulář editace uživatele
     */
    public function editForm(): string
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = (new UserModel())->findByIdFull($id);

        if (!$user) {
            return $this->forbidden();
        }

        $this->view->old   = $user;
        $this->view->roles = Roles::all();

        return $this->render('users/edit');
    }

    /**
     * Uložení editace uživatele
     */
    public function edit(): string
    {
        $id    = (int) ($_GET['id'] ?? 0);
        $model = new UserModel();

        $data = [
            '_token'          => $_POST['_token'] ?? '',
            'email'           => trim($_POST['email'] ?? ''),
            'employee_number' => trim($_POST['employee_number'] ?? ''),
            'first_name'      => trim($_POST['first_name'] ?? ''),
            'last_name'       => trim($_POST['last_name'] ?? ''),
            'global_role'     => $_POST['global_role'] ?? Roles::default(),
            'active'          => isset($_POST['active']) ? 1 : 0,
        ];

        $errors = $this->validate($data, false);

        if ($errors) {
            $this->view->errors = $errors;
            $this->view->old    = array_merge($model->findByIdFull($id), $data);
            return $this->render('users/edit');
        }

        $model->update($id, $data);
        Url::redirect('/users');
    }

    /**
     * Validace vstupních dat
     *
     * @param array $data
     * @param bool  $requirePassword
     * @return array<string,string>
     */
    private function validate(array $data, bool $requirePassword): array
    {
        $errors = [];

        if (!Csrf::check($data['_token'] ?? '')) {
            $errors['_token'] = 'Neplatný CSRF token';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email je povinný';
        } elseif (!$this->isValidEmail($data['email'])) {
            $errors['email'] = 'Email nemá podporovaný tvar';
        }

        if (empty($data['employee_number'])) {
            $errors['employee_number'] = 'Číslo zaměstnance je povinné';
        } elseif (mb_strlen($data['employee_number']) > 50) {
            $errors['employee_number'] = 'Maximální délka je 50 znaků';
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Jméno je povinné';
        } elseif (mb_strlen($data['first_name']) > 100) {
            $errors['first_name'] = 'Jméno je příliš dlouhé';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Příjmení je povinné';
        } elseif (mb_strlen($data['last_name']) > 100) {
            $errors['last_name'] = 'Příjmení je příliš dlouhé';
        }

        if ($requirePassword) {
            if (empty($data['password'])) {
                $errors['password'] = 'Heslo je povinné';
            } elseif (mb_strlen($data['password']) < 8) {
                $errors['password'] = 'Heslo musí mít alespoň 8 znaků';
            }
        }

        if (!Roles::exists($data['global_role'] ?? '')) {
            $errors['global_role'] = 'Neplatná role';
        }

        return $errors;
    }

    /**
     * Validace emailu
     */
    private function isValidEmail(string $email): bool
    {
        return mb_strlen($email) <= 254
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
