<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Models\TokenModel;
use App\Models\UserModel;
use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use RuntimeException;
use Throwable;

final class UserActivationService
{
    public function __construct(
        private TokenService $tokenService = new TokenService(),
        private UserModel $userModel = new UserModel(),
        private TokenModel $tokenModel = new TokenModel(),
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

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userModel->activateUser($userId, $hash);


        $this->tokenModel->commit();

    } catch (Throwable $e) {
        $this->tokenModel->rollback();
        throw $e;
    }
}


public function consumeAndProcess(string $rawToken, string $type, ?string $password = null): void
{
    $this->tokenModel->begin();

    try {

        $token = $this->tokenService->consume($rawToken, $type);
//var_dump($token);exit;
        $userId = (int)$token['user_id'];
        $email = $token['email'];

        switch ($type) {

            case TokenType::COMPANY_CREATE:
                $this->handleCompanyCreate($email, $password);
                break;

            case TokenType::INVITATION:
                $this->handleInvitation($userId, $password);
                break;

            case TokenType::PASSWORD_RESET:
                $this->handlePasswordReset($userId, $password);
                break;

            default:
                throw new RuntimeException('Neznámý typ tokenu.');
        }

        $this->tokenModel->commit();

    } catch (Throwable $e) {
        $this->tokenModel->rollback();
        throw $e;
    }
}


private function handleInvitation(int $userId, ?string $password): void
{
    if (!$password) {
        throw new RuntimeException('Chybí heslo.');
    }

    $user = $this->userModel->findRawById($userId);

    if (!$user) {
        throw new RuntimeException('Uživatel neexistuje');
    }

    if ((int)$user['active'] === 1) {
        throw new RuntimeException('Účet je již aktivní');
    }

    $this->userModel->activateUser($userId, $password);
}

private function handlePasswordReset(int $userId, ?string $password): void
{
    if (!$password) {
        throw new RuntimeException('Chybí nové heslo.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $this->userModel->setPassword($userId, $hash);
}

}
