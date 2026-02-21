<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\RegistrationRequestModel;
use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Core\Database;
use App\Core\Config;
use RuntimeException;

final class RegistrationService
{
    private int $tokenLifetimeMinutes = 60;

    public function __construct(
        private RegistrationRequestModel $requests,
        private CompanyModel $companies,
        private UserModel $users
    ) {}

    /* ==========================================================
     * STEP 1 – vytvoření / obnovení žádosti
     * ========================================================== */

    public function createRequest(string $email): string
    {
        $token = bin2hex(random_bytes(32));

        $this->requests->upsert([
            'email'       => $email,
            'token_hash'  => hash('sha256', $token),
            'expires_at'  => date('Y-m-d H:i:s', strtotime("+{$this->tokenLifetimeMinutes} minutes")),
            'ip_address'  => inet_pton($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);

        return $token;
    }

    /* ==========================================================
     * STEP 2 – validace tokenu (GET)
     * ========================================================== */

    public function validateToken(string $token): ?array
    {
        $hash = hash('sha256', $token);

        $request = $this->requests->findValidByHash($hash);
/*
        if (!$request) {
            throw new RuntimeException('Neplatný nebo expirovaný token.');
        }
*/
        return $request;
    }

    /* ==========================================================
     * STEP 2 – dokončení registrace (POST)
     * ========================================================== */

    public function complete(
        string $token,
        array $companyData,
        array $adminData
    ): void {

        $pdo = Database::admin();
        $pdo->beginTransaction();

        try {

            $request = $this->validateToken($token);
            /*
             * 1️⃣ Vytvoření firmy (admin DB)
             */
            $dbName = Config::get('registrationWorkDbName');
            
            $slug = $this->generateSlug($companyData['name']); 
            
            print_r($companyData['ico']);
            //print_r($slug);exit;
            
            $companyId = $this->companies->create([
            			'slug' 			=> $slug, // musíme vygenerovat někde
							'db_name' 		=> $dbName['registrationWorkDbName'],
							'name' 			=> $companyData['name'],
							'ico'  			=> $companyData['ico'],
							'active' 		=> 1,
							'created_at' 	=>  date('Y-m-d H:i:s'),//current_timestamp
							'activated_at' =>  date('Y-m-d H:i:s'),
            ]);

            /*
             * 2️⃣ Vytvoření admin uživatele
             */
            $this->users->createWithTenant($companyId,[
                
                'email'      			=> $request['email'],
                'employee_number' 	=> 'admin',
                'first_name' 			=> $adminData['first_name'],
	             'last_name'  			=> $adminData['last_name'],
                'password_hash'   			=> password_hash(
                    								$adminData['password'],
                    								PASSWORD_DEFAULT
                									),
                'global_role'       => 'admin',
                'domain_admin'		=> 1,
                'active'				=> 1,
                'created_at'			=>  date('Y-m-d H:i:s'), //current_timestamp
            ]);

            /*
             * 3️⃣ Smazání žádosti (token už nesmí existovat)
             */
            $this->requests->deleteById((int) $request['id']);

            $pdo->commit();

        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
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