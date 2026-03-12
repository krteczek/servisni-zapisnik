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

final class UserPasswordResetService
{
	private string $message = 'Pokud email a firma existují, byl Vám odeslán email s pokyny pro změnu hesla.';
	private const PASSWORD_RESET = 'PASSWORD_RESET';
    public function __construct(
        private RateLimiterService $rateLimiter = new RateLimiterService(),
        private TokenService $tokenService = new TokenService(),
        private UserModel $userModel = new UserModel(),
        private CompanyModel $companyModel = new CompanyModel(),
        private MailService $mailService = new MailService(),
    ) {}

    public function request(
        string $tenantSlug,
        string $email
    ): array {

        // 1️⃣ Rate limit (fake success pokud překročeno)
			$row = $this->rateLimiter->tooManyAttempts(
			    action:        self::PASSWORD_RESET,
             tenant:        $tenantSlug,
             email:         $email,
             maxAttempts:   3,
             windowMinutes: 15
			);
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
        if (!$company) {
				$ok = [
					"ok" => false,
					'result' => self::message,
					];
			    return $ok;
        }

        // 3️⃣ Najít uživatele
        $user = $this->userModel->findByEmailAndCompany(
            $email,
            (int)$company['id']
        );

        if (!$user || (int)$user['active'] !== 1) {
				$ok = [
					"ok" => false,
					'result' => self::message,
					];
			    return $ok;
        }

        // 4️⃣ Vytvořit token
        $token = $this->tokenService->create(
            userId: (int)$user['id'],
            type: AuthTokenService::TYPE_RESET_PASSWORD,
            ip: $ip,
            userAgent: $userAgent
        );

        // 5️⃣ Sestavit URL
        $resetUrl = Config::get('app.url')
            . '/reset-password?token=' . urlencode($token);

        // 6️⃣ Poslat email
        //musíme rozšířit informace kde a co
        $this->mailService->send(
            $email,
            $company['name'],
            'Reset hesla',
            "<a href='{$resetUrl}'>Reset hesla</a>",
            "Reset hesla: {$resetUrl}"
        );
    }
}