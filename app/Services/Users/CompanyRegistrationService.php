<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Services\Tokens\TokenService;
use App\Services\Tokens\TokenType;
use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Models\TaskModel;
use App\Models\WorkOrderModel;
use App\Models\TeamModel;
use App\Controllers\TeamController;
use App\Core\Config;
use App\Core\TenantContext;
use App\Core\LoggerHolder;
use Throwable;
use RuntimeException;
use PDO;
use App\Core\Transaction;
use App\Core\DatabaseScope;


final class CompanyRegistrationService
{
    public function __construct(
        private TokenService $tokenService = new TokenService(),
        private CompanyModel $companies = new CompanyModel(),
        private UserModel $users = new UserModel(),
        private TaskModel $tasks = new TaskModel(),
        private WorkOrderModel $orders = new WorkOrderModel(),
        private TeamModel $teams = new TeamModel()
    ) {}
    /* ========================================================== 
     * STEP 1 – vytvoření / obnovení žádosti
     * ========================================================== */

    public function createRequest(string $email): string
    {   

        try {
            $token = $this->tokenService->create(
                        TokenType::COMPANY_CREATE,
                        email:  $email,
                         );

            return $token;

        } catch (Throwable $e) {
		    LoggerHolder::get()->error('CompanyRegistrationService.createRequest failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),
		                'data'    => json_encode([$email]),
            ]);
            throw new RuntimeException('Failed to create company registration request');
        }
    }
    /*
     * ==========================
     * ADMIN část
     * ==========================
     */
  /**
    * @param array{
    *     name: string,
    *     ico: string,
    *     email: string,
    *     first_name: string,
    *     last_name: string,
    *     password: string
    * } $data
    * @return array{
    *     ok: true,
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
    * }|array{
    *     ok: false
    *    
    * }
    */
 public function completeAdmin(array $data): array
    {
        try {
            $dbName = Config::get('registrationWorkDbName.registrationWorkDbName');
            $slug   = $this->generateSlug($data['name']);

            $companyId = $this->companies->create([
                'slug' => $slug,
                'db_name' => $dbName,
                'name' => $data['name'],
                'ico' => $data['ico'],
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'activated_at' => date('Y-m-d H:i:s'),
            ]);

            $userId = $this->users->createWithTenant($companyId, [
                'email' => $data['email'],
                'employee_number' => 'admin',
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
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
                    'email'        => $data['email'],
                    'company_id'   => $companyId,
                    'company_name' => $data['name'],
                    'slug'         => $slug,
                    'first_name'   => $data['first_name'],
                    'last_name'    => $data['last_name'],
                    'global_role'  => 'admin',
                    'db_name'      => $dbName,
                    'team_id'      => $teamId,
                ],
            ];

        } catch (Throwable $e) {
		    LoggerHolder::get()->error('CompanyRegistrationService.createRequest failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),
		                'data'    => json_encode([$data]),
            ]);

            return ['ok' => false];
 
         }
    }

    /*
     * ==========================
     * WORK část
     * ==========================
     */
    /**
 * @param array{
 * 
 *     company_id: int,
 *     user_id: int,
 *     team_id: int,
 *     db_name: string
 * } $data
 * @return array{
 *     ok: bool
 * }
 */    

    public function completeWork(array $data): array
    {
        try {
            TenantContext::set($data['company_id']);
            /**
             * 1 Vytvoření první defaultní zakázky. Ta slouží jako ukázka a
             * zároven pro úkoly čistě firemního charakteru.
             * company_id, title, description, source, priority, status, created_by_user_id, is_system
             */

            $description1 = '
Vítejte v Bó systému.
---------------------

**Režijní práce** jsou běžné práce vykonávané pro fungování samotné firmy.

Například čas strávený vytvořením účtu v našem systému a seznámení se s ním,
se dá považovat za režijní náklad firmy.

K téhle zakázce je systémem vytvořeno několik prvních úkolů pro seznámení se s naším systémem.

            ';

            $WOID = $this->orders->createWithSequence($data['company_id'], [
                'title' => 'Režie firmy',
                'description' => $description1,
                'priority' => 'normal',
                'status' => 'in_progress',
                'created_by_user_id' => $data['user_id'],
            ]);

            if (!$WOID) {
                return ['ok' => false];
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
  - vytvořit další týmy
 - Až budete součástí týmu **Základní tým**, můžete napsat Report (nebo více) a tento úkol uzavřít.

Tip: Pokud nemůžete na Kartě úkolu najít tlačítko **Přidat Report**, nejste členem týmu, který má úkol na starosti.

Tip: Pokud v detailu úkolu nemůžete najít tlačítko **Uzavřít úkol**, tak k tomu úkolu nebyl napsán ani jeden Report.


';
            $this->tasks->createWithTenant($data['company_id'], [
                'team_id' => $data['team_id'],
                'work_order_id' => $WOID,
                'title' => '#1: Přidejte svůj účet do Základního týmu',
                'description' => $description2,
                'status' => 'open',
                'created_by_user_id' => $data['user_id'],
            ]);

            


            $description3 = '
Máte první tým, jste jeho členem, vytvořil jste první Report o splnění úkolu a možná jste i úkol označil jako Uzavřený.

Dalším Vaším úkolem bude přidat (pozvat) vaše spolupracovníky (pokud nějaké máte) do Bó systému:
 - Menu: Uživatelé > Přidat uživatele
 - Uživatelům budou poslány emaily s informacemi o přístupu do systému a možností nastavení hesla.
 - Až budete hotovi, opět vypište Report a úkol ukončete.

Systém funguje tak, že si volně můžete založit firmu v Bó systému. Spolupracovníkům potom vytváříte účty a tím je pozýváte do Bó systému.

';
			$TID2 = $this->tasks->createWithTenant($data['company_id'], [
                'team_id' 				=> $data['team_id'],
                'work_order_id'			=> $WOID,
                'title' 				=> '#2: Pozvěte spolupracovníky',
                'description' 			=> $description3,
                'status' 				=> 'open',
                'created_by_user_id' 	=> $data['user_id'],

            ]);

            return ['ok' => true];


        } catch (Throwable $e) {
		    LoggerHolder::get()->error('CompanyRegistrationService.createRequest failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),
		                'data'    => json_encode([$data]),
            ]);

            return ['ok' => false];
        }
    }

    /*
     * ==========================
     * SLUG
     * ==========================
     */
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