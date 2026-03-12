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
        $token = $this->tokenService->consume($rawToken, "activate");
        $userId = (int) $token["user_id"];

        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new RuntimeException("Uživatel neexistuje");
        }

        if ((int)$user["active"] === 1) {
            throw new RuntimeException("Účet je již aktivní");
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userModel->activateUser($userId, $hash);


        $this->tokenModel->commit();

    } catch (Throwable $e) {
        $this->tokenModel->rollback();
        throw $e;
    }
}


public function consumeAndProcess(string $rawToken, string $type, ?string $password = null, array $companyData = []): array
{
    $this->tokenModel->begin();

    //try {

        $token = $this->tokenService->consume($rawToken, $type);
			//var_dump($token);exit;

			// návrat vždy array
			if($token["ok"] === false)
			{
				return $token;
			}
        $userId = (int)$token["user_id"];
        $email = $token["email"];

			$row["ok"] = false;
        switch ($type) {

            case TokenType::COMPANY_CREATE:
                $companyData["email"] = $email;
                $companyData["tokenId"] = $token["id"];
                $row = $this->handleCompanyCreate(
                   data:     $companyData);
                break;

            case TokenType::INVITATION:
                $row = $this->handleInvitation($userId, $password);
                break;

            case TokenType::PASSWORD_RESET:
                $row = $this->handlePasswordReset($userId, $password);
                break;

            default:
            	$row["ok"] = false;
               $row["result"] = "Neznámý typ tokenu.";
        }
		  if($row["ok"] === true)
		  {
        		$this->tokenModel->commit();
        		return $row;
        }

        $this->tokenModel->rollback();
        return $row;

    //} catch (Throwable $e) {
    //    $this->tokenModel->rollback();
    //    throw $e;
    //}
}


private function handleInvitation(int $userId, ?string $password): array
{
    if (!$password) {
        return ["ok" => false, "result" => "Chybí nové heslo"];
    }

    $user = $this->userModel->findRawById($userId);

    if (!$user) {
        return ["ok" => false, "result" => "Uživatel neexistuje"];
    }

    if ((int)$user["active"] === 1) {
    	  return ["ok" => false, "result" => "Účet je již aktivní"];

    }

    $ok = [];
    $row = $this->userModel->activateUser($userId, $password);
    if(!$row)
    {
    	$ok["ok"] = false;
    	$ok["result"] = "Uživatele se nepodařilo aktivovat.";

    } else
    {
    	$ok["ok"] = true;
    	$ok["result"] = "Uživatel byl úspěšně aktivován.";

    }

    return $ok;

}

private function handlePasswordReset(int $userId, ?string $password): array
{
	 $ok = [];
    if (!$password) {
    	$ok = [
    	     "ok" => false,
    	     "result" => "Chybí nové heslo."
    	     ];
    	return $ok;
    }


    $hash = password_hash($password, PASSWORD_DEFAULT);
    $row = $this->userModel->setPassword($userId, $hash);
    if(!$row)
    {
    	$ok["ok"] = false;
    	$ok["result"] = "Heslo nebylo změněno.";

    } else
    {
    	$ok["ok"] = true;
    	$ok["result"] = "Heslo bylo úspěšně změněno.";

    }

    return $ok;
}



private function handleCompanyCreate(array $data): array
{

    //Musím zavolat: CompanyRegistrationService->complete(), dodkončit vytvoření noivého firemního uživatele vfčetně tenantu
    $row = (new CompanyRegistrationService())->complete(
               companyData:  $data
               );
      //var_dump($row);exit;
               return $row;
}



}
