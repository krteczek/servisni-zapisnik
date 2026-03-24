<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Models\TokenModel;
use App\Models\UserModel;
use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Core\Session;
use App\Core\LoggerHolder;
use RuntimeException;
use Throwable;
use App\Core\TenantContext;

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
	public function activate(int $userId, string $newPassword): void
	{
	    try {
	
	        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
	        $userModel = new UserModel(); // 🔥 NOVĚ
	        $userModel->activateUser($userId, $hash);
	
	
	        $this->tokenModel->commit();
	
	    } catch (Throwable $e) {
	        $this->tokenModel->rollback();
   
				LoggerHolder::get()->error('UserActivation failed', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),

				    'userId'    => $userId,

				]);
       }
	}


	public function consumeAndProcess(string $rawToken, string $type, ?string $password = null, array $companyData = []): array
	{
	   $this->tokenModel->begin();
	   $row = [
	      "ok" => false,
	      "result" => "Nezpracováno"
	   ];
	   try {
		   $token = $this->tokenService->consume($rawToken, $type);

			// návrat vždy array
			if($token["ok"] === false)
			{
			   $this->tokenModel->rollback();
			   return $token;
			}

		   $userId = (int)$token["user_id"];
		   $email = $token["email"];

		   // 🔥 dohledání usera BEZ tenant filtru
		   $user = $this->userModel->findRawById($userId);

			if (!$user) {
			   $this->tokenModel->rollback();
			   return [
			     "ok" => false,
			     "result" => "Uživatel neexistuje."
			   ];
			}

		   // 🔥 tady získáš tenant
		   $companyId = (int)$user['company_id'];

		   // 🔥 nastavíš tenant context
		   TenantContext::set($companyId);


	      switch ($type) {

	         case TokenType::COMPANY_CREATE:
		         $companyData["email"] = $email;
		         $companyData["tokenId"] = $token["id"];
		         $row = $this->handleCompanyCreate(
		                     data: $companyData
		                 );
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
	   } catch (Throwable $e) {

	      $this->tokenModel->rollback();

	      LoggerHolder::get()->error('UserActivation failed', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),

				    'userId'    => $userId ?? null,

				    'token'     => substr($rawToken, 0, 20) . '...',
				]);


	   } finally {
	      TenantContext::clear();
	   }
	   return $row;
	}


	private function handleInvitation(int $userId, ?string $password): array
	{
	    if (!$password) {
	        return ["ok" => false, "result" => "Chybí nové heslo"];
	    }
	    $userModel = new UserModel(); // 🔥 NOVĚ
	    $user = $userModel->findRawById($userId);

	    if (!$user) {
	        return ["ok" => false, "result" => "Uživatel neexistuje"];
	    }

	    if ((int)$user["active"] === 1) {
	    	  return ["ok" => false, "result" => "Účet je již aktivní"];

	    }

	    $ok = [];
	    $hash = password_hash($password, PASSWORD_DEFAULT);

	    $row = $userModel->activateUser($userId, $hash);
	    if($row['ok'] === false)
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
	    $userModel = new UserModel(); // 🔥 NOVĚ
	    $row = $userModel->setPassword($userId, $hash);//vrací to, co vrací db
	    if(!$row)
	    {
	    	$ok["ok"] = false;
	    	$ok["result"] = "Heslo nebylo změněno.";

	    } else
	    {
	    	$ok["ok"] = true;
	    	$ok["result"] = "Heslo bylo úspěšně změněno.";

	    }
	//error_log('SET PASSWORD USER: ' . $userId);
	//error_log('HASH: ' . $hash);
	//error_log('RESULT: ' . json_encode($ok));
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
