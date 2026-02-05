<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Core\Roles;
use App\Core\Flash;
use App\Core\UserGuard;
use App\Core\Mailer;
use App\Models\UserModel;
use App\Services\AuthTokenService;
use App\Core\Auth;

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

    $userId = $this->users->insert([
        'email'           => strtolower(trim($data['email'])),
        'employee_number' => trim($data['employee_number']),
        'first_name'      => trim($data['first_name']),
        'last_name'       => trim($data['last_name']),
        'global_role'     => $data['global_role'],
        'active'          => 0, // ⬅️ DŮLEŽITÉ: neaktivní do aktivace
        'created_at'      => date('Y-m-d H:i:s'),
    ]);

    /* ===== AKTIVAČNÍ TOKEN ===== */

    $tokenService = new AuthTokenService();
    $token = $tokenService->create(
        userId: $userId,
        type: 'activate',
        ttl: '+7 days'
    );

    // TODO: tady jen hook – vlastní MailService máš jinde
    // MailService::sendActivationMail($data['email'], $token);

    Flash::success('Uživatel byl vytvořen. Aktivační e-mail byl odeslán.');

    Url::redirect('/users');
}

    /* =============================
     * EDIT
     * ============================= */

    public function edit(int $id): string
    {
        $user = $this->users->find($id);
        if (!$user) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/users');
        }
        /* uživateli jde editovat jen některé položky
    	if (UserGuard::isProtected($user)) {
			Flash::error('Tento účet nelze upravovat.');
			Url::redirect('/users');
		}
*/
        $this->view->old   = $user;
        $this->view->roles = Roles::effective();

        return $this->render('users/edit');
    }

    public function update(int $id): string
    {
       $old = $this->users->find($id);
        if (!$old) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/users');
        }
        /* uživateli jde editovat jen některé položky
    	if (UserGuard::isProtected($old)) {
			Flash::error('Tento účet nelze upravovat.');
			Url::redirect('/users');
		}
 */
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
            Flash::success('Data uživatele ' . $data['first_name'] . ' ' . $data['last_name'] . ' byla změněna.');
        } else {
            Flash:error('Data uživatele se nepodařilo změnit.');
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

        //$password1 = $data['new_password'] ?? '';
        //$password2 = $data['new_password_confirm'] ?? '';

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

public function userDetail(int $id): string
{
    $user = $this->users->find($id);
    if (!$user) {
        Flash::error('Uživatel neexistuje.');
        Url::redirect('/users');
    }
/*
    if (UserGuard::isProtected($user)) {
        Flash::error('Tento účet nelze zobrazit.');
        Url::redirect('/users');
    }
*/
    $this->view->user = $user;

    // odvozený stav pro view
    $this->view->accountState = match (true) {
        $user['password_hash'] === null => 'pending_activation',
        (int)$user['active'] === 0      => 'inactive',
        default                         => 'active',
    };

    return $this->render('users/detail');
}

public function resendActivationEmail(int $id): string
{
    $user = $this->users->find($id);
    if (!$user) {
        Flash::error('Uživatel neexistuje.');
        Url::redirect('/users');
    }

    if ($user['password_hash'] !== null) {
        Flash::error('Účet je již aktivní.');
        Url::redirect('/users/' . $id . '/detail');
    }

    try {
        (new \App\Services\AuthTokenService())->createActivationToken(
            (int) $user['id'],
            (int) $user['company_id']
        );

        Flash::success('Aktivační e-mail byl znovu odeslán.');
    } catch (\Throwable $e) {
        error_log('[resendActivationEmail] ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        Flash::error('Nepodařilo se odeslat aktivační e-mail.');
    }

    Url::redirect('/users/' . $id . '/detail');
}

public function sendResetPassword(int $id): string
{
    $user = $this->users->find($id);
    if (!$user) {
        Flash::error('Uživatel neexistuje.');
        Url::redirect('/users');
    }

    if (!$user['active'] || !$user['password_hash']) {
        Flash::error('Tomuto uživateli nelze resetovat heslo.');
        Url::redirect('/users/' . $id . '/detail');
    }
    // oprávnění
if (!Auth::hasRole(['admin', 'mistr'])) {
    Flash::error('Na tuto akci nemáte oprávnění.');
    Url::redirect('/users/' . $id . '/detail');
}
    try {
        $service = new AuthTokenService();

        // volitelně: kontrola limitu v modelu
        $token = $service->create(
            (int) $user['id'],
            (int) $user['company_id'],
            'reset_password',
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            1 // expirace ve,  dnech → reálně 10–15 min řešit v service
        );
        $company = Auth::company();
        //var_dump($company);exit;

			Mailer::sendResetPassword($user['email'], $token, $company);
			Flash::success('E-mail pro změnu hesla byl odeslán.');
        	
    } catch (\Throwable $e) {
    	print_r('[sendResetPassword] ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());exit;
    	Flash::error('Reset hesla se nepodařilo odeslat.');
	}

    Url::redirect('/users/' . $id . '/detail');
}


}