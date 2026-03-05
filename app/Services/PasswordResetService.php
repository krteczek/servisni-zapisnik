<?php
declare(strict_types=1);

namespace App\Services;
use App\Services\RateLimiterService;
use App\Services\AuthTokenService;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Services\MailService;
use App\Core\Config;

final class PasswordResetService
{
    public function __construct(
        private AuthTokenService $tokenService,
        private UserModel $userModel,
    ) {}

    public function resetByToken(string $token, string $password): void
    {
        $row = $this->tokenService->consume(
            $token,
            AuthTokenService::TYPE_RESET_PASSWORD
        );

        $user = $this->userModel->findRawById((int)$row['user_id']);

        if (!$user) {
            throw new \LogicException('User not found');
        }

        $this->userModel->setPassword(
            (int)$user['id'],
            password_hash($password, PASSWORD_DEFAULT)
        );
    }
}