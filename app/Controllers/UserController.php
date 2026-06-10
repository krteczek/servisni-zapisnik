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

use App\Models\UserModel;

use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Services\Mail\MailService;
use App\Services\Users\BuildMailService;


final class UserController extends Controller
{
    private UserModel $users;

    //private const MAX_EMAIL_LENGTH           = 255;
    private const MAX_EMPLOYEE_NUMBER_LENGTH = 50;
    private const MAX_FIRST_NAME_LENGTH      = 100;
    private const MAX_LAST_NAME_LENGTH       = 100;
    //private const MAX_PASSWORD_LENGTH        = 255;
    //private const MIN_PASSWORD_LENGTH        = 8;

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

    /**
     * NOVÁ verze (správná)
     */
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
	
	    $userId = $this->users->create([
	        'email'           => strtolower(trim($data['email'])),
	        'employee_number' => trim($data['employee_number']),
	        'telefon' 		  => trim($data['telefon']),
	        
	        'first_name'      => trim($data['first_name']),
	        'last_name'       => trim($data['last_name']),
	        'global_role'     => $data['global_role'],
	        'active'          => 0,
	        'password_hash'   => null,
	    ]);
	
	    if (!$userId || (int)$userId <= 0) {
	        $this->addError('global', 'Litujeme, uživatele se nepodařilo vytvořit');
	        return $this->render('users/create');
	    }
	
	    try {

	    	//vytvoříme token
				$token = (new TokenService())->create(
				    type: TokenType::INVITATION,
				    email: $data['email'],
				    userId: $userId
				);

				$url = Url::base() . Url::to('/activate/complete?token=' . $token);
				$to = [
				    'activationUrl' => $url,
				    'companyName'   => Auth::company(),
				];
			//vytvoříme emailovou zprávu
			[$subject, $htmlBody, $textBody] = BuildMailService::build('users.invitation', $to);

			//pošleme email
        $ok = (new MailService())->send(
  				toEmail: $data['email'],
				toName: $data['email'],
				subject: $subject,
				html: $htmlBody,
				text: $textBody
        );
	
	
	        Flash::success(
	            'Uživatel: ' . $data['first_name'] . ' ' . $data['last_name'] .
	            ' byl úspěšně vytvořen. Aktivační e-mail byl odeslán.'
	        );
	
	    } catch (\Throwable $e) {
	//var_dump($e);
	        // ideálně logovat $e
	        Flash::error(
	            'Uživatel: ' . $data['first_name'] . ' ' . $data['last_name'] .
	            ' byl vytvořen, ale aktivační e-mail se nepodařilo odeslat.' 
	        );
	    }
	
	    Url::redirect('/{tenant}/users/' . (int)$userId . '/detail/#main');
	}


    /* =============================
     * EDIT
     * ============================= */

    public function edit(int $id): string
    {
        $user = $this->users->find($id);
        if (!$user) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/{tenant}/users/#main');
        }

        $this->view->old   = $user;
        $this->view->roles = Roles::effective();

        return $this->render('users/edit');
    }

    public function update(int $id): string
    {
        $old = $this->users->find($id);
        if (!$old) {
            Flash::error('Uživatel neexistuje.');
            Url::redirect('/{tenant}/users/#main');
        }

        $data = $_POST;

        $this->view->data  = $data;
        $this->view->old   = $old;
        $this->view->roles = Roles::effective();

        $this->checkCsrf();
        $this->validateUserData($data, $old);

        if ($this->hasErrors()) {
            return $this->render('users/edit');
        }

        $arr1 = [
            'telefon'			=> trim(trim($data['telefon'])),
            'employee_number' => trim($data['employee_number']),
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
        ];
        $arr2 = [];
        //pokud není uživatel doménový Superadmin, povolíme editovat:
        // email, globas_role s active
			if (!UserGuard::isProtected($old))
			{
                 $data['active'] = isset($data['active']) ? 1 : 0;

				$arr2 = [
				'email'           => strtolower(trim($data['email'])),
				'global_role'     => $data['global_role'],
				'active'          => $data['active'],
				];

                // uživatel byl deaktivován,. jdeme ho i odhlásit, pokud je přihlášený
                if ($data['active'] === 0 && $old['active'] === 1)
                    {
                        $data['session_version'] = $old['session_version'] + 1;
                        $arr2['session_version'] = $data['session_version'];
                    }

			}

			$update = array_merge($arr1, $arr2);

        if ($this->users->update($id, $update)) {
            Flash::success('Data byla změněna.');
        } else {
            Flash::error('Data se nepodařilo změnit.');
        }

        Url::redirect('/{tenant}/users/#main');
    }

    /* =============================
     * VALIDACE
     * ============================= */

    private function validateUserData(array $data, ?array $old = null): void
    {
        $email          = strtolower(trim($data['email'] ?? ''));
        $employeeNumber = trim($data['employee_number'] ?? '');
		  $telefon        = trim($data['telefon'] ?? '');
        $firstname      = trim($data['first_name'] ?? '');
        $lastname       = trim($data['last_name'] ?? '');
        $role 				= trim($data['global_role'] ?? '');

        if ($firstname === '') {
            $this->addError('firstname', 'Jméno je povinné.');
        }
        elseif (mb_strlen($firstname,'utf-8') >= self::MAX_FIRST_NAME_LENGTH)
        {
            $this->addError('firstname', 'Jméno je příliš dlouhé.');
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
    }

    /* =============================
     * DETAIL
     * ============================= */

    public function userDetail(int $id): string
    {
        $user = $this->users->find($id);
        if (!$user) {
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
//var_dump($user);exit;
        if (!$user) {
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
	            ' se nepodařilo aktivační e-mail odeslat. 1'
	            );

	       }
	
	    } catch (\Throwable $e) {
		    LoggerHolder::get()->error('UserController.resendActivationEmail: failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),


		    ]);
	    	
			//var_dump($e);exit;
	        
	        Flash::error(
	            'Uživateli: ' . $user['first_name'] . ' ' . $user['last_name'] .
	            ' se aktivační e-mail nepodařilo odeslat.2'
	        );
	    }
	
	    Url::redirect('/{tenant}/users/' . $id . '/detail/#main');
    }


 }
