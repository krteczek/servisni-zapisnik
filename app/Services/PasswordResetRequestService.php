<?php
declare(strict_types=1);

namespace App\Services;
use App\Services\RateLimiterService;
use App\Services\AuthTokenService;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Services\MailService;
use App\Core\Config;

final class PasswordResetRequestService
{
    public function __construct(
        private RateLimiterService $rateLimiter,
        private AuthTokenService $tokenService,
        private UserModel $userModel,
        private CompanyModel $companyModel,
        private MailService $mailService,
    ) {}

    public function request(
        string $tenantSlug,
        string $email,
        string $ip,
        string $userAgent
    ): void {

        // 1️⃣ Rate limit (fake success pokud překročeno)
if ($this->rateLimiter->tooManyAttempts(
    'reset_password',
    $tenantSlug,
    $email,
    $ip,
    3,
    15
)) {
    return;
}
        // 2️⃣ Najít firmu
        $company = $this->companyModel->findBySlug($tenantSlug);
        if (!$company) {
            return; // fake success
        }

        // 3️⃣ Najít uživatele
        $user = $this->userModel->findByEmailAndCompany(
            $email,
            (int)$company['id']
        );

        if (!$user || (int)$user['active'] !== 1) {
            return; // fake success
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