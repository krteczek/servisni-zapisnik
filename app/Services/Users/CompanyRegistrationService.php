<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Models\TokenModel;

use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Models\TaskModel;
use App\Models\WorkOrderModel;
use App\Models\TeamModel;
use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Services\Mail\MailService;
use App\Core\LoggerHolder;
use App\Controllers\TeamController;
use App\Core\Database;
use App\Core\Config;
use App\Core\TenantContext;
use RuntimeException;

/*
	Registrace do Bo systému
*/

final class CompanyRegistrationService
{

    public function __construct(
        private TokenService $tokenService = new TokenService(),
        //private TokenModel $tokenModel     = new TokenModel(),
        private CompanyModel $companies    = new CompanyModel,
        private UserModel $users           = new UserModel(),
        private TaskModel $tasks           = new TaskModel(),
        private WorkOrderModel $orders     = new WorkOrderModel,
        private TeamModel $teams           = new TeamModel()
    ) {}

    /* ==========================================================
     * STEP 1 – vytvoření / obnovení žádosti
     * ========================================================== */

    public function createRequest(string $email): string
    {
        $token = $this->tokenService->create(
                    TokenType::COMPANY_CREATE,
                    email:  $email,
                     );

        return $token;
    }

/**
 * @param array{
 *     name: string,
 *     ico: string,
 *     email: string,
 *     first_name: string,
 *     last_name: string,
 *     password: string
 * } $companyData
 *
 * @return array{
 *     ok: bool,
 *     data: array{
 *         user_id: int,
 *         email: string,
 *         company_id: int,
 *         company_name: string,
 *         slug: string,
 *         first_name: string,
 *         last_name: string,
 *         global_role: string,
 *         db_name: string,
 *         team_id: int
 *     }
 * }
 */    
public function completeAdmin(array $companyData): array
{
	 //zízkáme aktuální používanou db pro nové klienty
    $dbName = Config::get('registrationWorkDbName.registrationWorkDbName');
    $slug = $this->generateSlug($companyData['name']);

    $companyId = $this->companies->create([
        'slug' => $slug,
        'db_name' => $dbName,
        'name' => $companyData['name'],
        'ico' => $companyData['ico'],
        'active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'activated_at' => date('Y-m-d H:i:s'),
    ]);

    $userId = $this->users->createWithTenant($companyId, [
        'email' => $companyData['email'],
        'employee_number' => 'admin',
        'first_name' => $companyData['first_name'],
        'last_name' => $companyData['last_name'],
        'password_hash' => password_hash($companyData['password'], PASSWORD_DEFAULT),
        'global_role' => 'admin',
        'domain_admin' => 1,
        'active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    $teamId = $this->teams->createWithTenant($companyId, [
        'name' => 'Základní tým',
        'color' => TeamController::getDefaultColor(),
        'active' => 1
    ]);
    return [
	    'ok' => true,
	    'data' => [
	        'user_id'      => $userId,
	        'email'        => $companyData['email'],
	        'company_id'   => $companyId,
	        'company_name' => $companyData['name'],
	        'slug'         => $slug,
	        'first_name'   => $companyData['first_name'],
	        'last_name'    => $companyData['last_name'],
	        'global_role'  => 'admin',
	        'db_name'      => $dbName,
	        'team_id'      => $teamId,
	    ],
    ];
}

/**
 * @param array{
 *     company_id: int,
 *     user_id: int,
 *     team_id: int,
 *     db_name: string
 * } $data
 */    

public function completeWork(array $data): bool
{
    $companyId = $data['company_id'];
    $userId    = $data['user_id'];
    $teamId    = $data['team_id'];
    $dbName    = $data['db_name'];

    TenantContext::set($companyId);

        /*
         * 1 Vytvoření první defaultní zakázky. Ta slouží jako ukázka a
         * zároven pro úkoly čistě firemního charakteru.
         * company_id, title, description, source, priority, status, created_by_user_id, is_system
         */

        $description1 = '
          **Režijní práce** jsou běžné práce vykonávané pro fungování samotné firmy.

          Například čas strávený vytvořením účtu v našem systému a seznámení se s ním,
          se dá považovat za režijní náklad firmy.

          K téhle zakázce je systémem vytvořeno několik prvních úkolů pro seznámení se s naším systémem.

        ';

         
			$WOID = $this->orders->createWithSequence($companyId, [

			    'title' => 'Režie firmy',
			    'description' => $description1,
			    'priority' => 'normal',
			    'status' => 'in_progress',
			    'created_by_user_id' => $userId,

			]);
			if(!$WOID) {
				return false;
			}
        /*
         * 2 Vytvoření prvních úkolů k první defaultní zakázce.
         * tyto už bude možno dokončit běžným způsobem
         * company_id, team_id, work_order_id, title, description,
         * source, priority, status, created_by_user_id
         */
         $description2 = '
Vítejte v Bó systému.
---------------------

Vaším prvním úkolem bude přidat sám sebe do **Základního týmu**.
 - Menu: Týmy > Aktivní > Karta: Základní tým > Upravit
 - v rozhraní můžete:
  - sám sebe přidat a odebrat z týmu,
  - změnit barvu týmu
  - i jeho název
 - Až budete součástí týmu **Základní tým**, můžete napsat Report (nebo více) a tento úkol uzavřít.

Tip: Pokud nemůžete na Kartě úkolu najít tlačítko **Přidat Report**, nejste členem týmu, který má úkol na starosti.

Tip: Pokud v detailu úkolu nemůžete najít tlačítko **Uzavřít úkol**, tak k tomu úkolu nebyl napsán ani jeden Report.


';
			$TID1 = $this->tasks->createWithTenant($companyId, [
          'team_id' 					=> $teamId,
          'work_order_id'			=> $WOID,
          'title' 					=> '#1: Přidejte svůj účet do Základního týmu',
          'description' 			=> $description2,
          'status' 					=> 'open',
          'created_by_user_id' 	=> $userId,

]);
			if(!$TID1) {
				return false;
			}

         $description3 = '
Máte první tým, jste jeho členem, vytvořil jste první Report o splnění úkolu a možná jste i úkol označil jako Uzavřený.

Dalším Vaším úkolem bude přidat (pozvat) vaše spolupracovníky (pokud nějaké máte) do Bó systému:
 - Menu: Uživatelé > Přidat uživatele
 - Až budete hotovi, opět vypište Report a úkol ukončete.

Systém funguje tak, že si volně můžete založit firmu v Bó systému. Spolupracovníkům potom vytváříte účty a tím je pozýváte do Bó systému.

';

			$TID2 = $this->tasks->createWithTenant($companyId, [
          'team_id' 					=> $teamId,
          'work_order_id'			=> $WOID,
          'title' 					=> '#2: Pozvěte spolupracovníky',
          'description' 			=> $description3,
          'status' 					=> 'open',
          'created_by_user_id' 	=> $userId,

]);
			if(!$TID2) {
				return false;
			}
      return true;

}    


   /**
 * Pomocná metoda pro generování slugu
 */
	private function generateSlug(string $name): string
	{
	    // 1️⃣ Definice mapy diakritiky
	    $diacritic = [
	        'ä' => 'a', 'Ä' => 'A', 'á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A',
	        'ã' => 'a', 'Ã' => 'A', 'â' => 'a', 'Â' => 'A', 'č' => 'c', 'Č' => 'C',
	        'ć' => 'c', 'Ć' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ě' => 'e', 'Ě' => 'E',
	        'é' => 'e', 'É' => 'E', 'ë' => 'e', 'Ë' => 'E', 'è' => 'e', 'È' => 'E',
	        'ê' => 'e', 'Ê' => 'E', 'í' => 'i', 'Í' => 'I', 'ï' => 'i', 'Ï' => 'I',
	        'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ľ' => 'l', 'Ľ' => 'L',
	        'ĺ' => 'l', 'Ĺ' => 'L', 'ň' => 'n', 'Ň' => 'N', 'ń' => 'n', 'Ń' => 'N',
	        'ñ' => 'n', 'Ñ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ö' => 'o', 'Ö' => 'O',
	        'ô' => 'o', 'Ô' => 'O', 'ò' => 'o', 'Ò' => 'O', 'õ' => 'o', 'Õ' => 'O',
	        'ř' => 'r', 'Ř' => 'R', 'ŕ' => 'r', 'Ŕ' => 'R', 'š' => 's', 'Š' => 'S',
	        'ś' => 's', 'Ś' => 'S', 'ť' => 't', 'Ť' => 'T', 'ú' => 'u', 'Ú' => 'U',
	        'ů' => 'u', 'Ů' => 'U', 'ü' => 'u', 'Ü' => 'U', 'ù' => 'u', 'Ù' => 'U',
	        'û' => 'u', 'Û' => 'U', 'ý' => 'y', 'Ý' => 'Y', 'ž' => 'z', 'Ž' => 'Z',
	        'ź' => 'z', 'Ź' => 'Z', 'þ' => 'th', 'Þ' => 'th', 'ð' => 'dh', 'Ð' => 'dh',
	        'ß' => 'ss', 'œ' => 'oe', 'Œ' => 'OE'
	    ];
	    
	    // 2️⃣ Aplikace mapy diakritiky
	    $text = strtr($name, $diacritic);
	    
	    // 3️⃣ Odstranění všeho kromě písmen, číslic a mezer
	    $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
	    
	    // 4️⃣ Nahrazení mezer a podtržítek pomlčkami
	    $text = preg_replace('/[\s_]+/', '-', $text);
	    
	    // 5️⃣ Odstranění pomlček na začátku a konci
	    $text = trim($text, '-');
	    
	    // 6️⃣ Převod na malá písmena
	    $text = strtolower($text);
	    
	    // 7️⃣ Zkrácení
	    $text = substr($text, 0, 100);
	    
	    // 8️⃣ Unikátnost
	    $originalSlug = $text;
	    $counter = 1;
	    while ($this->companies->findBySlug($text)) {
	        $text = $originalSlug . '-' . $counter++;
	    }
	    
	    return $text;
	}

}