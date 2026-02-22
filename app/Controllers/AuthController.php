<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Url;
use App\Core\Session;
use App\Core\Mailer;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Models\RegistrationRequestModel;

use App\Services\AuthTokenService;
use App\Services\RegistrationTokenService;
use App\Services\RegistrationService;
use App\Services\ActivationMail;
use App\Core\Flash;

class AuthController extends Controller
{
    private const MAX_EMAIL_LENGTH           = 255;
    private const MAX_EMPLOYEE_NUMBER_LENGTH = 50;
    private const MAX_FIRST_NAME_LENGTH      = 100;
    private const MAX_LAST_NAME_LENGTH       = 100;
    private const MAX_PASSWORD_LENGTH        = 255;
    private const MIN_PASSWORD_LENGTH        = 8;
    private const MAX_COMPANY_NAME_LENGTH    = 255;
    private const MIN_COMPANY_NAME_LENGTH    = 2;
    private const ICO_LENGTH    					= 8;

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

    $this->view->csrf = $this->csrfField();
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

    $companyModel = new \App\Models\CompanyModel();
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
    $url = '/' . Auth::tenantSlug() . ($user['global_role'] === 'root' ? '/system' : '/tasks' );
    Url::redirect($url);

}



    public function logout(): string
    {
        Flash::success('Byl jste odhlášen. Přijďte zas!');

        Auth::logout();
        Url::redirect('/login');
    }
    


    /* =========================
     *  PUBLIC ROUTES
     * ========================= */

    public function activate(): string
    {
        return $this->handleTokenGet('activate');
    }

    public function activatePost(): string
    {
        return $this->handleTokenPost(
            'activate',
            function (int $userId, string $password): void {
                (new UserModel())->activateUser(
                    $userId,
                    password_hash($password, PASSWORD_DEFAULT)
                );
            },
            'Účet byl aktivován. Můžeš se přihlásit.'
        );
    }

    public function resetPassword(): string
    {
        return $this->handleTokenGet('reset_password');
    }


    public function resetPasswordPost(): string
    {		
        
        
        return $this->handleTokenPost(
            'reset_password',
            function (int $userId, string $password): void {
                (new UserModel())->setPassword(
                    $userId,
                    password_hash($password, PASSWORD_DEFAULT)
                );
            },
            'Heslo bylo změněno.'
        );
    }

	/* Zapomenuté heslo */
	public function forgotPassword(): string
	{
		return $this->render('auth/forgot-password');
	}

	public function forgotPasswordPost(): string
	{
			$this->checkCsrf();
			//email, tenant
			$tenant   = trim($_POST['tenant'] ?? '');
			$email    = trim($_POST['email'] ?? '');

			if ($tenant === '') {
				$this->addError('tenant', 'Firma je povinná');
			}
			
			if ($email === '') {
				$this->addError('email', 'Email je povinný');
			}

			if ($this->hasErrors()) {
				return $this->render('auth/forgot-password');
			}
			$user = null;
			$company = null;
			
			/* jdeme se zeptat db, zda něco takového (komninace emailu a tenantu) existuje */
			
		$company = (new CompanyModel())->findBySlug($tenant);

		if ($company) {
		    $user = (new UserModel())->findByEmailAndCompany(
		        $email,
		        (int) $company['id']
		    );
		
		    if ($user) {
		        try {
		            $token = (new AuthTokenService())->create(
		                userId: $user['id'],
		                type: AuthTokenService::TYPE_RESET_PASSWORD
		            );
		
		            Mailer::sendResetPassword(
		                $email,
		                $token,
		                $company['name']
		            );
		
		        } catch (\Throwable $e) {
		            error_log($e->getMessage());
		        }
		    }
		}
		
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

        if (!$token) {
            Flash::error('Chybí token.');
            Url::redirect('/login');
        }

        try {
        		
            $user = (new AuthTokenService())->validate($token, $type);

            $this->view->token = $token;
				$this->view->csrf  = $this->csrfField();

				return $this->render('auth/reset-password');

        } catch (\Throwable $e) {
        	//var_dump($e);exit;
            Flash::error('Odkaz je neplatný nebo expirovaný.');
            Url::redirect('/login');
        }
    }

    private function handleTokenPost(
        string $type,
        callable $userAction,
        string $successMessage
    ): string {
    	
			$this->checkCsrf();
			$token    = $_POST['token'] ?? null;
			$password = $_POST['password'] ?? null;
    		$passwordZ = $_POST['passwordZ'] ?? null;
    		if($password === '') 
    		{
    			$this->addError('password', 'Heslo je povinné');
    		} 
    		elseif (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) 
    		{
    			$this->addError('password', 'Heslo je příliš krátké');
    		} 
    		elseif($password !== $passwordZ) 
    		{
    			$this->addError('password', 'Hesla se neshodují, věnujte zápisu více pozornosti.');
    		}
    		
    		if ($this->hasErrors()) {
    			$this->view->token = $token;
            return $this->render('auth/reset-password');
        }	

        try {
            $service = new AuthTokenService();
            $row = $service->consume($token, $type);
            
				$userModel = new UserModel();

				/* načteme usera bez tenant omezení */
				$user = $userModel->findRawById((int)$row['user_id']);

				if (!$user) {
					throw new \LogicException('User not found');
				}

				/* bootstrap tenant do session */
				Session::set('user.company_id', (int)$user['company_id']);
				
            $userAction((int) $row['user_id'], $password);

            Flash::success($successMessage);
            Url::redirect('/login');

        } catch (\Throwable $e) {
        		$mess = '[handleTokenPost] ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            error_log($mess);
            Flash::error('Operace se nezdařila. ' . $mess);
            Url::redirect('/login');
        } finally {
				Session::forget('user.company_id');
        }
    }
    
	public function registerFormGet() {
		
		return $this->render('auth/create');
    
	}
public function registerFormPost()
{

}

	public function registrationStepOne()
	{
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
			
			$ip = ipToBinary(getClientIP());
			$ua = getClientUserAgent();
			
		 	$requests = new RegistrationRequestModel();
		 	$companies = new CompanyModel();
		 	$users = new UserModel();
			
			$token = (new RegistrationService($requests, $companies, $users))->createRequest(
				email: $data['email'],
			);
			
			$url = Url::base() . Url::to('/register/complete?token=' . $token);
			
			[$subject, $htmlBody, $textBody] = ActivationMail::build($url);
			//$ = ActivationMail::build($activationUrl);


        $ok = (new Mailer())->send(
  				toEmail: $data['email'],
				toName: $data['email'],
				subject: $subject,
				htmlBody: $htmlBody,
				textBody: $textBody
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
		$token = $_GET['token'] ?? null;
		$data = [];
		 $requests = new RegistrationRequestModel();
		 $companies = new CompanyModel();
		 $users = new UserModel();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') 
		{
			$token = $_POST['token'] ?? null;
			if (!$token || !(new RegistrationService($requests, $companies, $users))->validateToken($token)) 
			{
				// přesměrujeme na registraci znovu s Flash zprávou		    	
				// nebo raději nová stránka, text: registrace trvala příliš dlouho, zkuste to prosím rychleji
		    	Flash::error('Registrace trvala příliš dlouho, zkuste to prosím rychleji');
				Url::redirect('/register');
			}
		    
		    //return this->render('auth/register-step-two');

			$data = array_map(
					fn($value) => is_string($value) ? trim($value) : $value,
					$_POST
					);
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
			if (!preg_match('/^\d{8}$/', $data['ico'])) {
				$this->addError('ico', 'Neplatné IČO.');
			}

			$data['ico'] = trim($data['ico'] ?? '');
			if (empty($data['ico'] ?? '')) 
			{
				$this->addError('ico', 'IČO je povinné.');
			} 
			elseif (!preg_match('/^\d{' . self::ICO_LENGTH . '}$/', $data['ico'])) 
			{
				$this->addError('ico', 'IČO musí obsahovat ' . self::ICO_LENGTH . ' číslic.');
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
			$adminData['first_name'] =$data['first_name'];
			$adminData['last_name'] = $data['last_name'];
			$adminData['password'] = $data['password'];
			$companyData['name'] = $data['name'];
			$companyData['ico'] = $data['ico'];

			// Pokud validace prošla:
			// pokračujeme dál (model, transakce…)
			/*
                 'email'     => $request['email'],
                'slug'      => $slug,
                'password'  => $adminData['password'],
        ];			
			*/
			$ok = (new RegistrationService($requests, $companies, $users))->complete($token, $companyData, $adminData);
			if($ok['ok'] === true) 
			{
				//jdeme řešit přihlášení:
				$data = $ok['data'];
				print_r($data['db_name']['registrationWorkDbName']);exit;
			    Auth::login([
			        'id'           => $data['user_id'],
			        'email'        => $data['email'],
			        'global_role'  => 'admin',
			        'company_id'   => $data['company_id'],
			        'company_name' => $data['company_name'],
			        'tenant_slug'  => $data['slug'],
			        'first_name'   => $data['first_name'],
			        'last_name'    => $data['last_name'],
			        'db_name'      => $data['db_name']['registrationWorkDbName'],
			    ]);
			
			    Flash::success('Vítej v aplikaci, ' . ($user['first_name'] ?? $user['email']) . ' 👋'
			    );
			    $url = '/' . Auth::tenantSlug() . ($user['global_role'] === 'root' ? '/system' : '/tasks' );
			    Url::redirect($url);
			} 
			else 
			{
				var_dump($ok);
			}
		}  
		else 
		{
			$ok = (new RegistrationService($requests, $companies, $users))->validateToken($token);
			var_dump($ok);
			if (!$token || !$ok) 
			{
				// přesměrujeme na registraci znovu s Flash zprávou		    	
				// nebo raději nová stránka, text: registrace trvala příliš dlouho, zkuste to prosím rychleji
	    		Flash::error('Todo:Registrace trvala příliš dlouho, zkuste to prosím rychleji');
				return Url::redirect('/register');
			}
		}
		$data['token'] = $token;
		//prozatím aby se mi to protáčelo
		$this->view->data = $data;	
    
		return $this->render('auth/registrationStepTwo');
	}
}
