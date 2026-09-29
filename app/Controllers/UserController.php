<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Core\Roles;
use App\Core\Flash;
use App\Core\UserGuard;
use App\Core\Auth;
use App\Core\LoggerHolder;
use App\Core\ViewContext;
use App\Models\UserModel;
use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Services\Mail\MailService;
use App\Services\Users\BuildMailService;


final class UserController extends Controller
{
    private UserModel $users;
    private const MAX_EMPLOYEE_NUMBER_LENGTH = 50;
    private const MAX_FIRST_NAME_LENGTH      = 100;
    private const MAX_LAST_NAME_LENGTH       = 100;
 
    public function __construct(ViewContext $view)
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

    /**
     * NOVÁ verze (správná)
     */
    public function store(): string
    {
        $this->checkCsrf();
        $data = $this->validateUserData($_POST);

        if ($this->hasErrors()) {
            $this->view->roles = Roles::effective();
            $this->view->data  = $data;

            return $this->render('users/create');
        }

        try {
            $userId = $this->users->create([
                'email'           => strtolower(trim($data['email'])),
                'employee_number' => trim($data['employee_number']),
                'telefon'         => trim($data['telefon']),
                'first_name'      => trim($data['first_name']),
                'last_name'       => trim($data['last_name']),
                'global_role'     => $data['global_role'],
                'active'          => 0,
                'password_hash'   => null,
            ]);
        } catch (\Throwable $e) {
            LoggerHolder::get()->error('UserController.store: user creation failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->addError(
                'global',
                'Litujeme, uživatele se nepodařilo vytvořit.'
            );

            $this->view->roles = Roles::effective();
            $this->view->data  = $data;

            return $this->render('users/create');
        }

        try {
            // vytvoříme token
            $token = (new TokenService())->create(
                type: TokenType::INVITATION,
                email: $data['email'],
                userId: $userId
            );

            $url = Url::base() . Url::to(
                '/activate/complete?token=' . $token
            );

            $to = [
                'activationUrl' => $url,
                'companyName'   => Auth::company(),
            ];

            // vytvoříme emailovou zprávu
            [$subject, $htmlBody, $textBody] = BuildMailService::build(
                'users.invitation',
                $to
            );

            // pošleme email
            $ok = (new MailService())->send(
                toEmail: $data['email'],
                toName: $data['email'],
                subject: $subject,
                html: $htmlBody,
                text: $textBody
            );

            if ($ok) {
                Flash::success(
                    'Uživatel: ' . $data['first_name'] . ' ' . $data['last_name'] .
                    ' byl úspěšně vytvořen. Aktivační e-mail byl odeslán.'
                );
            } else {
                Flash::error(
                    'Uživatel: ' . $data['first_name'] . ' ' . $data['last_name'] .
                    ' byl vytvořen, ale aktivační e-mail se nepodařilo odeslat.'
                );
            }
        } catch (\Throwable $e) {
            LoggerHolder::get()->error(
                'UserController.store: activation email failed',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );

            Flash::error(
                'Uživatel: ' . $data['first_name'] . ' ' . $data['last_name'] .
                ' byl vytvořen, ale aktivační e-mail se nepodařilo odeslat.'
            );
        }

        Url::redirect('/{tenant}/users/' . $userId . '/detail/#main');
    }



    /* =============================
     * EDIT
     * ============================= */

    public function edit(int $id): string
    {
        $this->setSessionCheck('user_id', $id);
        $user = $this->users->find($id);
        if ($user === null) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/{tenant}/users/#main');
        }

        $this->view->old   = $user;
        $this->view->roles = Roles::effective();

        return $this->render('users/edit');
    }

    public function update(int $id): string
    {
        $this->confirmSessionCheck('user_id', $id, '/{tenant}/users/#main');
        $old = $this->users->find($id);
        if ($old === null) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/{tenant}/users/#main');
        }

        $this->view->roles = Roles::effective();
        $this->view->old   = $old;

        $this->checkCsrf();
        $data = $this->validateUserData($_POST);

        $this->view->data  = $data;
        if ($this->hasErrors()) {                    
            $this->setSessionCheck('user_id', $id);
            return $this->render('users/edit');
        }

        $arr1 = [
            'telefon'		  => trim($data['telefon']),
            'employee_number' => trim($data['employee_number']),
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
        ];
        $arr2 = [];
        //pokud není uživatel doménový Superadmin, povolíme editovat:
        // email, globas_role s active
        if (!UserGuard::isProtected($old))
        {
            $active = isset($_POST['active']) ? 1 : 0;
            $arr2 = [
                'email'           => strtolower(trim($data['email'])),
                'global_role'     => $data['global_role'],
                'active'          => $active,
            ];

            // uživatel byl deaktivován,. jdeme ho i odhlásit, pokud je přihlášený
            if ($active === 0 && (int)$old['active'] === 1)
                {
                    $arr2['session_version'] = (int)$old['session_version'] + 1;
                }

        }

		$update = array_merge($arr1, $arr2);

        if ($this->users->update($id, $update)) {
            Flash::success('Data byla změněna.');
        } else {
            $this->setSessionCheck('user_id', $id);
            Flash::error('Data se nepodařilo změnit.');
            return $this->render('users/edit');
        }

        Url::redirect('/{tenant}/users/#main');
    }

    /** =============================
     *   VALIDACE
     *  =============================
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function validateUserData(array $data): array
    {
        $email          = strtolower(trim($data['email'] ?? ''));
        $employeeNumber = trim($data['employee_number'] ?? '');
		$telefon        = trim($data['telefon'] ?? '');
        $firstname      = trim($data['first_name'] ?? '');
        $lastname       = trim($data['last_name'] ?? '');
        $role 			= trim($data['global_role'] ?? '');

        if ($firstname === '') {
            $this->addError('first_name', 'Jméno je povinné.');
        }
        elseif (mb_strlen($firstname,'utf-8') > self::MAX_FIRST_NAME_LENGTH)
        {
            $this->addError('first_name', 'Jméno je příliš dlouhé.');
        }

        if ($lastname === '') {
            $this->addError('last_name', 'Příjmení je povinné.');
        }
        elseif (mb_strlen($lastname,'utf-8') > self::MAX_LAST_NAME_LENGTH)
        {
            $this->addError('last_name', 'Příjmení je příliš dlouhé.');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Email neodpovídá platnému formátu.');
        }

        if ($employeeNumber === '') {
            $this->addError('employee_number', 'Osobní číslo je povinné.');
        }
        elseif (mb_strlen($employeeNumber,'utf-8') > self::MAX_EMPLOYEE_NUMBER_LENGTH)
        {
            $this->addError('employee_number', 'Osobní číslo je příliš dlouhé.');
        }
        if(!Roles::exists($role))
        {
            $role = Roles::default();
        }

        return [
            'email'             => $email,
            'employee_number'   => $employeeNumber,
            'telefon'           => $telefon,
            'first_name'        => $firstname,
            'last_name'         => $lastname,
            'global_role'       => $role,
        ];
    }

    /* =============================
     * DETAIL
     * ============================= */

    public function userDetail(int $id): string
    {
        $user = $this->users->find($id);
        if ($user === null) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/{tenant}/users/#main');
        }


        $user['accountState'] = match (true) {
            $user['password_hash'] === null => 'pending_activation',
            (int)$user['active'] === 0      => 'inactive',
            default                         => 'active',
        };

        $this->view->user = $user;


        return $this->render('users/detail');
    }

    /* =============================
     * RESEND ACTIVATION
     * ============================= */

    public function resendActivationEmail(int $id): string
    {
        $user = $this->users->find($id);

        if ($user === null) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/{tenant}/users/#main');
        }

        if ($user['password_hash'] !== null) {
            Flash::error('Účet je již aktivní.');
            Url::redirect('/{tenant}/users/' . $id . '/detail/#main');
        }
        
	    try {
				$token = (new TokenService())->create(
				    type: TokenType::INVITATION,
				    email: $user['email'],
				    userId: $id
				);

				$url = Url::base() . Url::to('/activate/complete?token=' . $token);

				$to = [
				    'activationUrl' => $url,
				    'companyName'   => Auth::company(),
				];
			//vytvoříme emailovou zprávu
			[$subject, $htmlBody, $textBody] = BuildMailService::build('users.invitation', $to);
        $ok = (new MailService())->send(
  				toEmail: $user['email'],
				toName: $user['email'],
				subject: $subject,
				html: $htmlBody,
				text: $textBody
        );

	       if($ok)
	       {
	       		Flash::success(
	            'Uživateli: ' . $user['first_name'] . ' ' . $user['last_name'] .
	            ' byl aktivační e-mail úspěšně odeslán.'
	            );
	       }
	       else
	       {
	       		Flash::error(
	            'Uživateli: ' . $user['first_name'] . ' ' . $user['last_name'] .
	            ' se nepodařilo aktivační e-mail odeslat.'
	            );

	       }
	
	    } catch (\Throwable $e) {
		    LoggerHolder::get()->error('UserController.resendActivationEmail: failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),


		    ]);
	    	
	        
	        Flash::error(
	            'Uživateli: ' . $user['first_name'] . ' ' . $user['last_name'] .
	            ' se aktivační e-mail nepodařilo odeslat.'
	        );
	    }
	
	    Url::redirect('/{tenant}/users/' . $id . '/detail/#main');
    }


 }
