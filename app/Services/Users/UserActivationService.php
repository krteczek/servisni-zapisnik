<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Models\TokenModel;
use App\Models\UserModel;
use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Core\LoggerHolder;
use App\Core\Database;
use Throwable;
use App\Core\TenantContext;

final class UserActivationService
{
    public function __construct(
        private TokenService $tokenService = new TokenService(),
        private UserModel $userModel = new UserModel(),
    ) {}

    public function consumeAndProcess(
        string $rawToken,
        string $type,
        ?string $password = null,
        array $companyData = []
    ): array {

        try {
            $token = $this->tokenService->consume($rawToken, $type);

            if ($token["ok"] === false) {
                return $token;
            }

            return match ($type) {

                TokenType::COMPANY_CREATE =>
                    $this->processCompanyCreate($token, $companyData),

                TokenType::INVITATION,
                TokenType::PASSWORD_RESET =>
                    $this->processUserToken($token, $type, $password),

                default => [
                    "ok" => false,
                    "result" => "Neznámý typ tokenu."
                ],
            };

        } catch (Throwable $e) {

            LoggerHolder::get()->error('UserActivation failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'token'   => substr($rawToken, 0, 20) . '...',
            ]);

            return [
                "ok" => false,
                "result" => "Zpracování tokenu selhalo."
            ];
        } finally {
            TenantContext::clear();
        }
    }

	private function processCompanyCreate(array $token, array $companyData): array
	{
	    $companyData["email"]   = $token["email"];
	    $companyData["tokenId"] = $token["id"];

	    return (new OnboardingService())->run($companyData); // 🔥 místo CompanyRegistrationService
	}

    private function processUserToken(
        array $token,
        string $type,
        ?string $password
    ): array {

        if (!$this->hasUser($token)) {
            return [
                "ok" => false,
                "result" => "Token neobsahuje uživatele."
            ];
        }

        $userId = (int)$token["user_id"];

        $user = $this->userModel->findRawById($userId);

        if (!$user) {
            return [
                "ok" => false,
                "result" => "Uživatel neexistuje."
            ];
        }

        TenantContext::set((int)$user['company_id']);

        return match ($type) {
            TokenType::INVITATION     => $this->handleInvitation($user, $password),
            TokenType::PASSWORD_RESET => $this->handlePasswordReset($userId, $password),

            default => [
                "ok" => false,
                "result" => "Neznámý typ tokenu."
            ]
        };
    }

    private function handleInvitation(array $user, ?string $password): array
    {
        if (!$password) {
            return ["ok" => false, "result" => "Chybí nové heslo"];
        }


        if ((int)$user["active"] === 1) {
            return ["ok" => false, "result" => "Účet je již aktivní"];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $row = $this->userModel->activateUser((int) $user['id'], $hash);

        return $row['ok'] === false
            ? ["ok" => false, "result" => "Uživatele se nepodařilo aktivovat."]
            : ["ok" => true, "result" => "Uživatel byl úspěšně aktivován."];
    }

    private function handlePasswordReset(int $userId, ?string $password): array
    {
        if (!$password) {
            return [
                "ok" => false,
                "result" => "Chybí nové heslo."
            ];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $row = $this->userModel->setPassword($userId, $hash);

        return !$row
            ? ["ok" => false, "result" => "Heslo nebylo změněno."]
            : ["ok" => true, "result" => "Heslo bylo úspěšně změněno."];
    }

    private function hasUser(array $token): bool
    {
        return !empty($token['user_id']);
    }
}