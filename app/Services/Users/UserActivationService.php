<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Models\TokenModel;
use App\Models\UserModel;
use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Services\Onboarding\OnboardingService;
use App\Services\Mail\MailService;
use App\Core\LoggerHolder;
use App\Core\Database;
use App\Core\Transaction;
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
        string $password

    ): array {

        if (!$password) {
            return [
                "ok" => false,
                "result" => "Chybí nové heslo."
            ];
        }

        try {
			    $result = Transaction::run(
			        function () use ($password, $rawToken, $type) {

			            $tokenData = $this->tokenService->consume($rawToken, $type);

			            if ($tokenData['ok'] === false) {
			                return $tokenData;
			            }

				        if (!$this->hasUser($tokenData)) {
				            return [
				                "ok" => false,
				                "result" => "Token neobsahuje uživatele."
				            ];
				        }
				        $userId = (int)$tokenData["user_id"];

				        $user = $this->userModel->findByIdWithoutTenant($userId);
				        if (!$user) {
				            return [
				                "ok" => false,
				                "result" => "Uživatel neexistuje."
				            ];
				        }
							if (empty($user['company_id'])) {
							    throw new \LogicException('User has no company_id');
							}

				        TenantContext::set((int)$user['company_id']);

			            if($type === TokenType::INVITATION)
			            {
                        return $this->handleInvitation($user, $password);
			            }

			            if($type === TokenType::PASSWORD_RESET)
			            {
                        return $this->handlePasswordReset($user, $password);
			            }

			            throw new \InvalidArgumentException("Unknown Token type: {$type}");

			        },
			        'admin'
			    );

			    if ($result['ok'] === false) {
			        return [
				                "ok" => false,
				                "result" => $result["result"],
				            ];
			    }
             return [
				     "ok" => true,
				     "result" => $result["result"],
				 ];


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


    private function handleInvitation(array $user, string $password): array
    {

        if ((int)$user["active"] === 1) {
            return ["ok" => false, "result" => "Účet je již aktivní"];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $row = $this->userModel->activateUser((int) $user['id'], $hash);

        return $row['ok'] === false
            ? ["ok" => false, "result" => "Uživatele se nepodařilo aktivovat."]
            : ["ok" => true, "result" => "Uživatel byl úspěšně aktivován."];
    }

    private function handlePasswordReset(array $user, string $password): array
    {
        if ((int)$user["active"] !== 1) {

	        try {
	        	   // pošleme email:
	        	     //[$subject, $htmlBody, $textBody] = BuildMailService::($user);
	        	     [$subject, $htmlBody, $textBody] = BuildMailService::build('user.not-active',
	        	     ['user' => $user,]);

			        $ok = (new MailService())->send(
			  				toEmail:  $user['email'],
							toName:   $user['first_name'] . ' ' . $user['last_name'],
							subject:  $subject,
							html:     $htmlBody,
							text:     $textBody
                 );


	        } catch (Throwable $e) {
	            LoggerHolder::get()->error('UserActivation send email  for not active failed.', [
	                'message' => $e->getMessage(),
	                'file'    => $e->getFile(),
	                'line'    => $e->getLine(),
	                'trace'   => $e->getTraceAsString(),
	                'token'   => substr($rawToken, 0, 20) . '...',
	            ]);


	        }
           return [
                "ok" => false,
                "result" => "Váš účet není aktivní, zřejmě byl administrátorem pozastaven..."];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $row = $this->userModel->setPassword($user['id'], $hash);

        return !$row
            ? ["ok" => false, "result" => "Heslo nebylo změněno."]
            : ["ok" => true, "result" => "Heslo bylo úspěšně změněno."];
    }

    private function hasUser(array $token): bool
    {
        return !empty($token['user_id']);
    }
}