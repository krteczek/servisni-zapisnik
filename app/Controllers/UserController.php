<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Url;
use App\Core\ViewContext;

use App\Models\UserModel;

class UserController extends Controller
{
	private UserModel $model;

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->model = new UserModel();
    } 
    /**
     * Výpis uživatelů
     */
    public function index(): string
    {
        $model = new UserModel();
        $this->view->users = $this->model->all();

        return $this->render('users/index');
    }

    /**
     * Formulář pro vytvoření uživatele
     */
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
/**
 * Uložení nového uživatele (admin vytváří cizí účet)
 */
public function store(): string
{
    $data = [
        '_token'          => trim($_POST['_token'] ?? ''),
        'email'           => trim($_POST['email'] ?? ''),
        'employee_number' => trim($_POST['employee_number'] ?? ''),
        'first_name'      => trim($_POST['first_name'] ?? ''),
        'last_name'       => trim($_POST['last_name'] ?? ''),
        'password'        => $_POST['new_password'] ?? '',
        'password_confirm'=> $_POST['new_password_confirm'] ?? '',
        'global_role'     => $_POST['global_role'] ?? Roles::default(),
    ];

    $errors = $this->validate($data);

    if ($this->model->existsByEmail($data['email'])) {
        $errors['email'] = 'Email už existuje';
    }

    if ($this->model->existsByEmployeeNumber($data['employee_number'])) {
        $errors['employee_number'] = 'Číslo zaměstnance už existuje';
    }

    if ($data['password'] !== $data['password_confirm']) {
        $errors['password'] = 'Hesla se neshodují';
    }

    if ($errors) {
        $this->view->errors = $errors;
        $this->view->old    = $data;
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

    Url::redirect('/users');
}

    /**
     * Editační formulář
     */
    public function editForm(int $id): string
    {
        //$id = (int) ($_GET['id'] ?? 0);

        $user = $this->model->findByIdFull($id);
        if (!$user) {
            return $this->forbidden();
        }

        $this->view->old   = $user;
        $this->view->roles = Roles::all();

        return $this->render('users/edit');
    }

    /**
     * Uložení úprav uživatele
     */
    public function edit(int $id): string
    {
        //$id = (int) ($_GET['id'] ?? 0);

        $data = [
            '_token'          => trim($_POST['_token'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
            'employee_number' => trim($_POST['employee_number'] ?? ''),
            'first_name'      => trim($_POST['first_name'] ?? ''),
            'last_name'       => trim($_POST['last_name'] ?? ''),
            'global_role'     => $_POST['global_role'] ?? Roles::default(),
            'active'          => isset($_POST['active']) ? 1 : 0,
        ];

        $errors = $this->validate($data, false);

        if ($this->model->existsByEmail($data['email'], $id)) {
            $errors['email'] = 'Email už existuje';
        }

        if ($this->model->existsByEmployeeNumber($data['employee_number'], $id)) {
            $errors['employee_number'] = 'Číslo zaměstnance už existuje';
        }

        if ($errors) {
            $this->view->errors = $errors;
            $this->view->old = array_merge($this->model->findByIdFull($id), $data);
            return $this->render('users/edit');
        }

        $this->model->update($id, $data);

        Url::redirect('/users');
    }

/**
 * Zobrazí formulář pro změnu hesla uživatele
 */
public function passwordForm(int $id): string
{
    //$id = (int) ($_GET['id'] ?? 0);

	
    $data = $this->model->findById($id);

    if (!$data) {
        Url::redirect('/users');
        //return;
    }
    
    //$this->view->errors = $errors;
    $this->view->data = $data;
    
    return $this->render('users/password');
}

/**
 * Zpracuje změnu hesla uživatele
 */
/**
 * Zpracuje změnu hesla uživatele (admin mění cizí heslo)
 */
public function updatePassword(int $id): string
{
    // $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        return $this->forbidden();
    }

    $user = $this->model->findById($id);
    if (!$user) {
        Url::redirect('/users');
    }

    $password = $_POST['new_password'] ?? '';
    $confirm  = $_POST['new_password_confirm'] ?? '';

    $errors = [];

    if ($password === '') {
        $errors['password'] = 'Heslo je povinné';
    } elseif (mb_strlen($password) < 8) {
        $errors['password'] = 'Heslo musí mít alespoň 8 znaků';
    } elseif ($password !== $confirm) {
        $errors['password'] = 'Hesla se neshodují.';
    }

    if ($errors) {
        $this->view->data   = $user;
        $this->view->errors = $errors;
        return $this->render('users/password');
    }

    $this->model->updatePassword(
        $id,
        password_hash($password, PASSWORD_DEFAULT)
    );

    Url::redirect('/users');
}


    /**
     * Validace vstupních dat
     */
    private function validate(array $data, bool $requirePassword = true): array
    {
        $errors = [];

        if (!Csrf::check($data['_token'] ?? '')) {
            $errors['_token'] = 'Neplatný CSRF token';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Email je povinný';
        } elseif (!$this->isValidEmail($data['email'])) {
            $errors['email'] = 'Email nemá podporovaný tvar';
        }

        if ($requirePassword) {
            if ($data['password'] === '') {
                $errors['password'] = 'Heslo je povinné';
            } elseif (mb_strlen($data['password']) < 8) {
                $errors['password'] = 'Heslo musí mít alespoň 8 znaků';
            }
        }

        if ($data['employee_number'] === '') {
            $errors['employee_number'] = 'Číslo zaměstnance je povinné';
        } elseif (mb_strlen($data['employee_number']) > 50) {
            $errors['employee_number'] = 'Číslo zaměstnance je příliš dlouhé';
        }

        if (!Roles::exists($data['global_role'])) {
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
