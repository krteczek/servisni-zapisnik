<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Services\Guards\RateLimiterService;

use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Services\Tokens\TokenResult;
use App\Services\Mail\MailService;

use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Core\Config;
use App\Core\Url;

final class UserPasswordResetService
{
	private string $message = 'Pokud email a firma existují, byl Vám odeslán email s pokyny pro změnu hesla.';


    public function __construct(
        private RateLimiterService $rateLimiter = new RateLimiterService(),
        private TokenService $tokenService = new TokenService(),
        private UserModel $userModel = new UserModel(),
        private CompanyModel $companyModel = new CompanyModel(),
        private MailService $mailService = new MailService(),
    ) {}

    /**
     * @param string $tenantSlug
     * @param string $email
     * @return array{ok: bool, result: string}
     */
    public function request(
        string $tenantSlug,
        string $email
    ): array {
//var_dump(self::PASSWORD_RESET);exit;
        // 1️⃣ Rate limit (fake success pokud překročeno)
			$row = $this->rateLimiter->tooManyAttempts(
			    action:        TokenType::PASSWORD_RESET,
             tenant:        $tenantSlug,
             email:         $email
			);

			//var_dump($row);
			if($row === true)
			{
				// příliš mnoho požadavků v krátkém čase,
				$ok = [
					"ok" => false,
					'result' => $this->message,
					];
			    return $ok;
			}

        // 2️⃣ Najít firmu
        $company = $this->companyModel->findBySlug($tenantSlug);
        if ($company === null) {
				$ok = [
					"ok" => false,
					'result' => $this->message,
					];
			    return $ok;
        }

        // 3️⃣ Najít uživatele
        $user = $this->userModel->findByEmailAndCompany(
            $email,
            (int)$company['id']
        );

        if ($user === null || (int)$user['active'] !== 1) {
				$ok = [
					"ok" => false,
					'result' => $this->message,
					];
			    return $ok;
        }

        // 4️⃣ Vytvořit token
        $token = $this->tokenService->create(
                  type:       TokenType::PASSWORD_RESET,
                  email:      $email,
                  userId:     (int)$user['id']
        );



        // 5️⃣ Sestavit URL
        $resetUrl = Url::base() . Url::to('/reset-password?token=' . urlencode($token));

        $to = [
            'activationUrl' => $resetUrl,
            'companyName'   => $company['name'],
        ];
        // 6️⃣ Poslat email
        //musíme rozšířit informace kde a co
        [$subject, $html, $text] = BuildMailService::build('users.reset-password', $to);
        $mail = $this->mailService->send(
        toEmail:  $email,
        toName:   $company['name'],
        subject:  $subject,
        html:     $html,
        text:     $text
        );
        if ($mail === false)
        {
				$ok = [
					"ok" => false,
					'result' => $this->message,
					];
			    return $ok;

        }

        else
        {
        	   $ok = $user;
				$ok["ok"] = true;
				$ok['result'] = $this->message;

			    return $ok;

        }

    }

}