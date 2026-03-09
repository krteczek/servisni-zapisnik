<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuthTokenModel;
use App\Models\UserModel;
use RuntimeException;
use Throwable;

final class UserActivationService
{
    public function __construct(
        private AuthTokenService $tokenService = new AuthTokenService(),
        private UserModel $userModel = new UserModel(),
        private AuthTokenModel $tokenModel = new AuthTokenModel(),
    ) {}

    /**
     * Aktivace účtu + nastavení hesla
     */
public function activate(string $rawToken, string $newPassword): void
{
    $this->tokenModel->begin();

    try {
        $token = $this->tokenService->consume($rawToken, 'activate');
        $userId = (int) $token['user_id'];

        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new RuntimeException('Uživatel neexistuje');
        }

        if ((int)$user['active'] === 1) {
            throw new RuntimeException('Účet je již aktivní');
        }

        //$this->userModel->setPassword($userId, $newPassword);
        $this->userModel->activateUser($userId, $newPassword);


        $this->tokenModel->commit();

    } catch (Throwable $e) {
        $this->tokenModel->rollback();
        throw $e;
    }
}}
