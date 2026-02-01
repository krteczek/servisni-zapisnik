<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuthTokenModel;
use App\Models\UserModel;
use RuntimeException;
use Throwable;

final class PasswordResetService
{
    public function __construct(
        private AuthTokenService $tokenService = new AuthTokenService(),
        private UserModel $userModel = new UserModel(),
        private AuthTokenModel $tokenModel = new AuthTokenModel(),
    ) {}

    /**
     * Reset hesla pomocí tokenu
     */
    public function reset(string $rawToken, string $newPassword): void
    {
        $this->tokenModel->begin();

        try {
            $token = $this->tokenService->consume($rawToken, 'reset_password');

            $userId = (int) $token['user_id'];

            $user = $this->userModel->find($userId);
            if (!$user) {
                throw new RuntimeException('Uživatel neexistuje');
            }

            if ((int)$user['active'] !== 1) {
                throw new RuntimeException('Účet není aktivní');
            }

            $this->userModel->updatePassword($userId, $newPassword);

            $this->tokenModel->commit();
        } catch (Throwable $e) {
            $this->tokenModel->rollback();
            throw $e;
        }
    }
}
