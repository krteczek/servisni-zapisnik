<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Url;
use App\Core\Session;
use App\Core\Mailer;
use App\Core\Flash;
use App\Core\LoggerHolder;

use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Models\RegistrationRequestModel;
use App\Models\TaskModel;
use App\Models\WorkOrderModel;
use App\Models\TeamModel;

use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Services\Users\CompanyRegistrationService;
use App\Services\Mail\MailService;
use App\Services\Users\UserActivationService;
use App\Services\Users\UserPasswordResetService;
use App\Services\Guards\RateLimiterService;
use App\Services\Users\BuildMailService;
use App\Services\Onboarding\OnboardingService;

use \Throwable;

class AuthController extends Controller
{
    private const MAX_EMAIL_LENGTH           = 255;
    //private const MAX_EMPLOYEE_NUMBER_LENGTH = 50;
    private const MAX_FIRST_NAME_LENGTH      = 100;
    private const MAX_LAST_NAME_LENGTH       = 100;
    private const MAX_PASSWORD_LENGTH        = 255;
    private const MIN_PASSWORD_LENGTH        = 8;
    private const MAX_COMPANY_NAME_LENGTH    = 255;
    private const MIN_COMPANY_NAME_LENGTH    = 2;
    private const ICO_LENGTH    					= 8;
    private const BAD_LOGIN                  = 'BAD_LOGIN';
    private const TOKEN_ERROR                = 'Odkaz je neplatný nebo expirovaný.';

    public function root(): string
    {
    		$url = '/' . Auth::tenantSlug() . (Auth::check() ? '/tasks' : '/login');
        Url::redirect($url);
    }

    public function loginForm(): string
    {
			if (Auth::check()) {
				Url::redirect('/' . Auth::tenantSlug() . '/tasks');
			} 

        $this->view->csrf   = $this->csrfField();
        $this->view->errors = [];
        $this->view->data   = [];

        return $this->render('auth/login');
    }

public function login(): string
{
		//var_dump($_POST);
    $tenant   = trim($_POST['tenant'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $this->view->data = [
        'tenant' => $tenant,
        'email'  => $email,
    ];

    /* ===== VALIDACE DAT ===== */

    if ($tenant === '') {
        $this->addError('tenant', 'Firma je povinná');
    }

    if ($email === '') {
        $this->addError('email', 'Email je povinný');
    }

    if ($password === '') {
        $this->addError('password', 'Heslo je povinné');
    }

    /* ===== CSRF ===== */

    $this->checkCsrf();

    if ($this->hasErrors()) {
        return $this->render('auth/login');
    }
	
    /* ===== AUTH ===== */

    $companyModel = new CompanyModel();
    $company = $companyModel->findBySlug($tenant);

    $user = null;
//var_dump($company);
    if ($company) {
        $userModel = new UserModel();
        $user = $userModel->findByEmailAndCompany(
            $email,
            (int) $company['id']
        );
        //var_dump($user);
    }

    if (
        !$user ||
        !password_verify($password, $user['password_hash'])
    ) {
        // 1️⃣ Rate limit
			$row = (new RateLimiterService())->tooManyAttempts(
			    action:        self::BAD_LOGIN,
             tenant:        $tenant,
             email:         $email,
			);
			if($row === true)
			{
				// příliš mnoho požadavků v krátkém čase,
				//tady nebudeme řešit, to už si vyřeší banservice
			}
    	  
        $this->addError('global', 'Neplatné přihlašovací údaje. Pokud problém přetrvává, kontaktujte správce vašeho prostoru.');
        return $this->render('auth/login');
    }

    /* ===== LOGIN OK ===== */
    Auth::login([
        'id'          => (int) $user['id'],
        'email'       => $user['email'],
        'global_role' => $user['global_role'],
        'company_id'  => (int) $company['id'],
        'company_name'     => $company['name'],
			'tenant_slug'     => $company['slug'],
        'first_name'  => $user['first_name'] ?? null,
        'last_name'   => $user['last_name'] ?? null,
        'db_name'		 => $company['db_name'],
    ]);

    Flash::success('Vítej v aplikaci, ' . ($user['first_name'] ?? $user['email']) . ' 👋'
    );
    $url = '/' . Auth::tenantSlug() . ($user['global_role'] === 'root' ? '/system' : '/tasks/#main' );
    Url::redirect($url);

}



    public function logout(): string
    {
        Flash::success('Byl jste odhlášen. Přijďte zas!');

        Auth::logout();
        Url::redirect('/login');
    }
    
private static function ensureGuestRedirect(): void
{
    if (Auth::check()) {
        Flash::info(
            'Tato akce je dostupná pouze pro nepřihlášené uživatele.'
        );

        Url::redirect('/' . Auth::tenantSlug() . '/tasks/#main');
    }
}

    /* =========================
     *  PUBLIC ROUTES
     * ========================= */

    public function activateGet(): string
    {
    	  self::ensureGuestRedirect();
        return $this->handleTokenGet(TokenType::INVITATION);
    }

    public function resetPasswordGet(): string
    {
    	  self::ensureGuestRedirect();
        return $this->handleTokenGet(TokenType::PASSWORD_RESET);
    }

public function activatePost(): string
{
	 self::ensureGuestRedirect();
    return $this->processToken(TokenType::INVITATION, 'Účet byl aktivován.');
}


public function resetPasswordPost(): string
{
	 self::ensureGuestRedirect();
    return $this->processToken(TokenType::PASSWORD_RESET, 'Heslo bylo změněno.');
}

private function processToken(string $type, string $successMessage): string
{
    $this->checkCsrf();

    $token    = trim($_POST['token'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $passwordZ = trim($_POST['passwordZ'] ?? '');

    // ===== VALIDACE =====

    if ($password === '') {
        $this->addError('password', 'Heslo je povinné');
    } elseif (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
        $this->addError('password', 'Heslo je příliš krátké');
    } elseif (mb_strlen($password) > self::MAX_PASSWORD_LENGTH) {
        $this->addError('password', 'Heslo je příliš dlouhé.');
    } elseif ($password !== $passwordZ) {
        $this->addError('password', 'Hesla se neshodují.');
    }

    if ($this->hasErrors()) {
        $this->view->data['token'] = $token;
        return $this->render('auth/reset-password');
    }

    // ===== PROCESS =====

    try {
        $ok = (new UserActivationService())->consumeAndProcess(
            $token,
            $type,
            $password
        );
        if ($ok['ok'] === true) {
        	 Flash::success($successMessage);
        	 Url::redirect('/login');
        }

     } catch (Throwable $e) {
		    LoggerHolder::get()->error('AuthController.processToken: failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),
		                'data'    => json_encode([$token, $type, $password]),

		    ]);
		}
		$this->addError('global', 'Něco se nepovedlo, zkuste to prosím později...');
		$this->view->data['token'] = $token;
      return $this->render('auth/reset-password');
}

	/* Zapomenuté heslo */
	public function forgotPassword(): string
	{
      self::ensureGuestRedirect();
		return $this->render('auth/forgot-password');
	}
	
public function forgotPasswordPost(): string
{
    $this->checkCsrf();

    $tenant = trim($_POST['tenant'] ?? '');
    $email  = trim($_POST['email'] ?? '');

    if ($tenant === '') {
        $this->addError('tenant', 'Firma je povinná');
    }

    if ($email === '') {
        $this->addError('email', 'Email je povinný');
    }

    if ($this->hasErrors()) {
        return $this->render('auth/forgot-password');
    }

     /*************************************************************************
     *    můžeme přistoupit k posílání emailu:   *
     *************************************************************************/
     $row = (new UserPasswordResetService())->request(
        tenantSlug:   $tenant,
             email:   $email
    );

    Flash::success(
        'Pokud účet existuje, odeslali jsme vám pokyny pro změnu hesla.'
    );

    Url::redirect('/login');
}

    /* =========================
     *  PRIVATE HELPERS
     * ========================= */

    private function handleTokenGet(string $type): string
    {
        $token = $_GET['token'] ?? null;
        $errMsg = self::TOKEN_ERROR;

        if (!$token) {
            Flash::error($errMsg);
            Url::redirect('/login');
        }
        try {
	        $row = (new TokenService())->validate($token, $type);
	        if ($row['ok'] === false) {
	            Flash::error($errMsg);
	            Url::redirect('/login');

	        }
       
        
        		
            $this->view->data = $row;
			
            $this->view->data['token'] = $token;

			return $this->render('auth/reset-password');

        } catch (\Throwable $e) {
		    LoggerHolder::get()->error('AuthController.handleTokenGet: failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),
		                'data'    => json_encode([$token, $type]),

		    ]);
        	
        	//var_dump($e);exit;
            Flash::error($errMsg);
            Url::redirect('/login');
        }
    }

    

	public function registrationStepOne():  string
	{
		self::ensureGuestRedirect();
		$data = [];
		if ($_SERVER['REQUEST_METHOD'] === 'POST') 
		{
	    	//$this->view->data = $data;
			$data = array_map(
			    fn($value) => is_string($value) ? trim($value) : $value,
			    $_POST
			);
			
			// --- EMAIL ---
			if (empty($data['email'] ?? '')) 
			{
				$this->addError('email', 'Email je povinný.');
			} 
			elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) 
			{
				$this->addError('email', 'Email nemá platný formát.');
			}
			elseif (mb_strlen($data['email']) > self::MAX_EMAIL_LENGTH) 
			{
				$this->addError('email', 'Email je příliš dlouhý.');
			}
			$this->checkCsrf();
    
			if ($this->hasErrors()) 
			{
				$this->view->data = $data;
				return $this->render('auth/registrationStepOne');
			}

			$token = (new CompanyRegistrationService())->createRequest($data['email']);

			
			$url = Url::base() . Url::to('/register/complete?token=' . $token);
			//print_r($url);
			[$subject, $htmlBody, $textBody] = BuildMailService::build('users.registration', ['activationUrl' => $url]);
			


        $ok = (new MailService())->send(
  				toEmail: $data['email'],
				toName: $data['email'],
				subject: $subject,
				html: $htmlBody,
				text: $textBody
        );

			if($ok) 
			{
				Url::redirect('/register/check-email');
			}
			$this->addError('global', 'Litujeme, nepodařilo se zaregistrovat Váš Email. Zkuste to prosím později. Děkujeme. Bó Team');
			
		}
    
    
    $this->view->data = $data;
    return $this->render('auth/registrationStepOne');
	
	}
	
	public function registrationStepOneSucces(): string
	{
		return $this->render('auth/registrationStepOneSucces');
	}
	
	public function registrationStepTwo(): string
	{
		self::ensureGuestRedirect();
      $errMsg = 'Odkaz je neplatný nebo expirovaný. Můžete si požádat o nový.';
		$token = $_GET['token'] ?? null;

		if(empty($token))
		{
         Flash::error($errMsg);
         Url::redirect('/login');

		}
		$data = [];
		$companies 	= new CompanyModel();

		$ok = (new TokenService())->validate($token,TokenType::COMPANY_CREATE);
     if ($ok['ok'] === false) {
         Flash::error($errMsg);
         Url::redirect('/login');

     }

 		if ($_SERVER['REQUEST_METHOD'] === 'POST') 
		{
			$data = array_map(
					fn($value) => is_string($value) ? trim($value) : $value,
					$_POST
					);
		    if(empty($data['token']) || !hash_equals($token, $data['token']))
		    {
                Flash::error($errMsg);
                Url::redirect('/register');
		    }
		    

			$this->checkCsrf();
    
			//odstranění mezer mezi čísly
			$data['ico'] = preg_replace('/\s+/', '', $data['ico']);

			// --- NAME ---
			if (empty(trim($data['name'] ?? ''))) 
			{
				$this->addError('name', 'Název firmy je povinný.');
 			} 
			elseif (mb_strlen($data['name']) < self::MIN_COMPANY_NAME_LENGTH) 
			{
				$this->addError('name', 'Název firmy je příliš krátký.');
			} 
				elseif (mb_strlen($data['name']) > self::MAX_COMPANY_NAME_LENGTH) 
			{
         	$this->addError('name', 'Název firmy je příliš dlouhý.');   
			}

			// --- ICO ---

			$data['ico'] = trim($data['ico'] ?? '');
			if (empty($data['ico'])) 
			{
				$this->addError('ico', 'IČO je povinné.');
			} 
			elseif (!preg_match('/^\d{' . self::ICO_LENGTH . '}$/', $data['ico'])) 
			{
				$this->addError('ico', 'IČO musí obsahovat ' . self::ICO_LENGTH . ' číslic.');
			} 
			//ověření neexistence iča v db (unikátní číslo v databázi)
			//vrací true když existuje
			elseif($companies->existsByIco($data['ico']))
			{
				$this->addError('ico', 'Firma s tímto IČO je již registrovaná');
			}
			
			// --- FIRST NAME ---
			if (empty(trim($data['first_name'] ?? '')))
			{
				$this->addError('first_name', 'Jméno je povinné.');
			}
    		elseif (mb_strlen($data['first_name']) > self::MAX_FIRST_NAME_LENGTH) 
			{
				$this->addError('first_name', 'Jméno je příliš dlouhé.');
			}

			// --- LAST NAME ---
			if (empty(trim($data['last_name'] ?? '')))
			{
				$this->addError('last_name', 'Příjmení je povinné.');
			}
			elseif (mb_strlen($data['last_name']) > self::MAX_LAST_NAME_LENGTH) 
			{
				$this->addError('last_name', 'Příjmení je příliš dlouhé.');
			}
			
			if($data['password'] === '') 
			{
					$this->addError('password', 'Heslo je povinné');
			} 
			elseif (mb_strlen($data['password']) < self::MIN_PASSWORD_LENGTH) 
			{
				$this->addError('password', 'Heslo je příliš krátké');
			} 
			elseif($data['password'] !== $data['passwordZ']) 
			{
				$this->addError('password', 'Hesla se neshodují, věnujte zápisu více pozornosti.');
			}
		
			// pokud chyby, zobrazíme znovu form
			if ($this->hasErrors()) 
			{
				$this->view->data = $data;
				return $this->render('auth/registrationStepTwo');
			}
			
			$payload = [
				'token' => $token,
				'type' => TokenType::COMPANY_CREATE,
				'data' => $data
			];
			$ok = (new OnboardingService())->run($payload);
			
			if($ok['ok'] === true) 
			{
				
				//jdeme řešit přihlášení:
				$d = $ok['data'];
				//print_r($data['db_name']['registrationWorkDbName']);
				Auth::login([
					'id'           => $d['user_id'],
					'email'        => $d['email'],
					'global_role'  => 'admin',
					'company_id'   => $d['company_id'],
					'company_name' => $d['company_name'],
					'tenant_slug'  => $d['slug'],
					'first_name'   => $d['first_name'],
					'last_name'    => $d['last_name'],
					'db_name'      => $d['db_name'],
				]);
			
				Flash::success('Vítej v aplikaci, ' . ($d['first_name'] ?? $d['email']) . ' 👋'
				);
				$url = '/' . Auth::tenantSlug() . '/tasks';
				Url::redirect($url);
			} 
			else 
			{
				//var_dump($ok);
				$this->addError('global', 'Litujeme, Váš účet se nepodařilo vytvořit. Zkuste to prosím za chvíli znovu.');
			}
		}
		
		$data['token'] = $token;
		//prozatím aby se mi to protáčelo
		$this->view->data = $data;	
    
		return $this->render('auth/registrationStepTwo');
	}
}
