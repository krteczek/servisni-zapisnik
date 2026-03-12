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
use App\Controllers\TeamController;
use App\Core\Database;
use App\Core\Config;
use RuntimeException;

/*
	Registrace do Bo systému
*/

final class CompanyRegistrationService
{

    public function __construct(
        private TokenService $tokenService = new TokenService(),
        private TokenModel $tokenModel     = new TokenModel(),
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

 
    /* ==========================================================
     * STEP 2 – dokončení registrace (POST)
     * ========================================================== */

 public function complete(
     array $companyData
): array {

	//var_dump($companyData);
//echo "joogggo<br>";
    $pdo = Database::admin();
    //$pdo->beginTransaction();

    try {


        /* $request = $this->tokenService->consume($companyData['token'], TokenType::COMPANY_CREATE);
var_dump($request);
echo "joogggo<br>";
        if ($request['ok'] === false) {
            //$pdo->rollBack();
            $request['error'] = 'Neplatný nebo expirovaný token.';
            return $request;
        }
        */

        /*
         * 1️⃣ Vytvoření firmy
         */
        $dbName = Config::get('registrationWorkDbName.registrationWorkDbName');
        $slug = $this->generateSlug($companyData['name']);

        $companyId = $this->companies->create([
            'slug'          => $slug,
            'db_name'       => $dbName,
            'name'          => $companyData['name'],
            'ico'           => $companyData['ico'],
            'active'        => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'activated_at'  => date('Y-m-d H:i:s'),
        ]);
//echo "joogggo 91 " . $companyId . "<br>";
        if(!$companyId)
        {
            //$pdo->rollBack();
            $companyId = [];
            $companyId['ok'] = false;
            $companyId['error'] = 'Nepodařilo se vytvořit Vaši firmu v Bó systému. Zkuste to prosím později.';
            return $companyId;

        }
//echo "joogggo 100 '" . $companyData['password'] . "'<br>";
//$passw = password_hash($companyData['password'],PASSWORD_DEFAULT);
//echo "hash '" . $passw . "'<br>";
        /*
         * 2️⃣ Vytvoření admin uživatele
         */
         $data = [
            'email'           => $companyData['email'],
            'employee_number' => 'admin',
            'first_name'      => $companyData['first_name'],
            'last_name'       => $companyData['last_name'],
            'password_hash'   => password_hash(
                $companyData['password'],
                PASSWORD_DEFAULT
            ),
            'global_role'     => 'admin',
            'domain_admin'    => 1,
            'active'          => 1,
            'created_at'      => date('Y-m-d H:i:s'),

         ];
         //var_dump($companyId, $data);
        $userId = $this->users->createWithTenant($companyId, $data);
//echo "joogggo 118 " . $userId . "<br>";
        if(!$userId)
        {
            //$pdo->rollBack();
            $userId = [];
            $userId['ok'] = false;
            $userId['error'] = 'Nepodařilo se vytvořit Vašeho Admina v Bó systému. Zkuste to prosím později.';
            return $userId;

        }

        /*
         * 3 Vytvoření první defaultní zakázky. Ta slouží jako ukázka a
         * zároven pro úkoly čistě firemního charakteru.
         * company_id, title, description, source, priority, status, created_by_user_id, is_system
         */

		/*
		 *	vytvoření prvního defaultního týmu
		 * name, color, active
		 */
		$TeamID = $this->teams->createWithTenant($companyId, [
			'name' 	=> 'Základní tým',
			'color' 	=> TeamController::getDefaultColor(),
			'active'	=> 1
		]);
        if(!$TeamID)
        {
            //$pdo->rollBack();
            $TeamID = [];
            $TeamID['ok'] = false;
            $TeamID['error'] = 'Nepodařilo se vytvořit Váš první tým v Bó systému. Zkuste to prosím později.';
            return $TeamID;

        }




$description1 = <<<TXT
**Režijní práce** jsou běžné práce vykonávané pro fungování samotné firmy.
Například čas strávený vytvořením účtu v našem systému a seznámení se s ním,
se dá považovat za režijní náklad firmy.
K téhle zakázce je systémem vytvořeno několik prvních úkolů.

TXT;

            Database::useWorkDatabase($dbName);
				$WOID = $this->orders->createWithTenant($companyId, [

    'title' => 'Režie firmy',
    'description' => $description1,
    'priority' => 'normal',
    'status' => 'in_progress',
    'created_by_user_id' => $userId,

]);
        if(!$WOID)
        {
            //$pdo->rollBack();
            $WOID = [];
            $WOID['ok'] = false;
            $WOID['error'] = 'Nepodařilo se vytvořit Váši první zakázku v Bó systému. Zkuste to prosím později.';
            return $WOID;

        }


        /*
         *  Vytvoření prvních úkolů k první defaultní zakázce.
         * tyto už bude možno dokončit běžným způsobem
         * company_id, team_id, work_order_id, title, description,
         * source, priority, status, created_by_user_id
         */
$description2 = <<<TXT
Vítejte v Bó systému servisního zápisníku.

Vaším prvním úkolem bude přidat sám sebe do **Základního týmu**.
 - Menu: Týmy > Aktivní > Karta: Základní tým > Upravit
 - v rozhraní můžete:
  - sám sebe přidat a odebrat z týmu,
  - změnit barvu týmu
  - i jeho název
 - Až budete součástí týmu **Základní tým**, můžete napsat Report (nebo více) a úkol uzavřít.

Tip: Pokud nemůžete na Kartě úkolu najít tlačítko **Přidat Report**, nejste členem týmu, který má úkol na starosti.

Tip: Pokud v detailu úkolu nemůžete najít tlačítko **Uzavřít úkol**, tak k tomu úkolu nebyl napsán ani jeden Report.


TXT;
				$TID1 = $this->tasks->createWithTenant($companyId, [
    'team_id' 					=> $TeamID,
    'work_order_id'			=> $WOID,
    'title' 					=> '#1: Přidejte svůj účet do Základního týmu',
    'description' 			=> $description2,
    'status' 					=> 'open',
    'created_by_user_id' 	=> $userId,

]);
        if(!$TID1)
        {
            //$pdo->rollBack();
            $TID1 = [];
            $TID1['ok'] = false;
            $TID1['error'] = 'Nepodařilo se vytvořit Váš první úkol v Bó systému. Zkuste to prosím později.';
            return $TID1;

        }

$description3 = <<<TXT
Máte první tým, jste jeho členem, vytvořil jste první Report o splnění úkolu a možná jste i úkol označil jako Uzavřený.

Dalším Vaším úkolem bude přidat (pozvat) vaše spolupracovníky (pokud nějaké máte) do Bó systému:
 - Menu: Uživatelé > Přidat uživatele
 - Až budete hotovi, opět vypište Report a úkol ukončete.

Systém funguje tak, že si volně můžete založit firmu v Bó systému. Spolupracovníkům potom vytváříte účty a tím je pozýváte do Bó systému.

TXT;

				$TID2 = $this->tasks->createWithTenant($companyId, [
    'team_id' 					=> $TeamID,
    'work_order_id'			=> $WOID,
    'title' 					=> '#2: Pozvěte spolupracovníky',
    'description' 			=> $description3,
    'status' 					=> 'open',
    'created_by_user_id' 	=> $userId,

]);
        if(!$TID2)
        {
            //$pdo->rollBack();
            $TID2 = [];
            $TID2['ok'] = false;
            $TID2['error'] = 'Nepodařilo se vytvořit Váš druhý úkol zakázku v Bó systému. Zkuste to prosím později.';
            return $TID2;

        }

         Database::admin();
        /*
         *  Smazání žádosti
         */
        $this->tokenModel->deleteById((int) $companyData['tokenId']);

			/*
			 *	Dokončíme transakci
			*/
        //$pdo->commit();

			/*
			 *	vrátíme data pro první přihlášení
			  */
			return [
			    'ok' => true,
			    'data' => [
			        'user_id' => $userId,
			        'email' => $companyData['email'],
			        'company_id' => $companyId,
			        'company_name' => $companyData['name'],
			        'slug' => $slug,
			        'first_name' => $companyData['first_name'],
			        'last_name' => $companyData['last_name'],
			        'global_role' => 'admin',
			        'db_name' => $dbName,
			    ]
			];
		
    } catch (\Throwable $e) {

        //$pdo->rollBack();
			error_log((string)$e);
        // Tohle je systémová chyba
        return [
            'ok' => false,
            'error' => 'Registraci se nepodařilo dokončit. Zkuste to prosím znovu. '
				
        ];
    }
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
}}