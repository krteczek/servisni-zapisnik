<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;
use App\Core\LoggerHolder;

/**
 * Model pro správu společností (tenantů) v multi-tenant architektuře.
 * Tabulka companies je globální – není tenant-aware, protože definuje samotné tenanty.
 *
 * Společnost = tenant = izolovaná instance aplikace s vlastní work databází.
 */
final class CompanyModel extends BaseModel
{
    /**
     * Název tabulky bez prefixu.
     *
     * @var string
     */
    protected string $table = 'companies';

    /**
     * Připojení k admin databázi (centrální registr tenantů).
     *
     * @var string
     */
    protected string $connection = 'admin';

    /**
     * Model NENÍ tenant-aware – společnosti definují tenanty, nepatří pod ně.
     *
     * @var bool
     */
    protected bool $tenantAware = false;

    /**
     * Zjistí, zda existuje společnost s daným slugem a vrátí její data.
     * Bez ohledu na aktivní stav – používá se pro interní validace.
     *
     * TODO: [PERFORMANCE] Index na sloupci slug je nezbytný
     *
     * @param string $slug Unikátní identifikátor společnosti (z URL)
     * @return array<string, mixed>|null Data společnosti nebo null
     */
    public function existsBySlug(string $slug): ?array
    {
         return $this->fetchOne(
            "SELECT *
             FROM {$this->tableName}
             WHERE slug = :slug
             LIMIT 1",
            ['slug' => $slug]
        );
  }

    /**
     * Ověří, zda existuje společnost s daným ID.
     *
     * @param int $id ID společnosti
     * @return bool TRUE pokud společnost existuje
     */
    public function existsById(int $id): bool
    {
        return (bool) $this->find($id);
    }

    /**
     * Najde aktivní společnost podle slugu.
     * Používá se při routování – pokud společnost neexistuje nebo není aktivní,
     * není možné se přihlásit ani zobrazit stránky.
     *
     * Očekává:
     * - Slug je unikátní
     * - Aktivní společnost = může se přihlásit a pracovat
     *
     * TODO: [BUSINESS] Přidat kontrolu expirace licence
     *
     * @param string $slug Slug společnosti z URL
     * @return array<string, mixed>|null Data společnosti nebo null
     */
		public function findBySlug(string $slug): ?array
		{
		    return $this->fetchOne(
		        "SELECT *
		         FROM {$this->tableName}
		         WHERE (slug = :slug OR ico = :ico)
		           AND active = 1
		         LIMIT 1",
		        [
		            'slug' => $slug,
		            'ico'  => $slug,
		        ]
		    );
		}

    /**
     * Vrátí seznam všech společností se základními informacemi.
     * Používá se v administraci pro přehled tenantů.
     *
     * TODO: [PERFORMANCE] Přidat stránkování při velkém počtu tenantů
     * TODO: [FEATURE] Přidat filtrování (active/inactive, datum vytvoření)
     *
     * @return array<int, array<string, mixed>> Seznam společností (id, name, slug, created_at)
     */
    public function findAll(): array
    {
        return $this->fetchAll(
            "SELECT *
             FROM {$this->tableName}
             ORDER BY name"
        );
    }
    
    
    public function activate(int $companyId): void
    {
        $this->update($companyId, [
            'active' => 1,
            'activated_at' => date('Y-m-d H:i:s'),
        ]);
    }

public function existsByIco(string $ico): bool
{
    $sql = "SELECT 1
            FROM {$this->tableName}
            WHERE ico = :ico
            LIMIT 1";

    $stmt = $this->db()->prepare($sql);
    $stmt->execute(['ico' => $ico]);

    return (bool) $stmt->fetchColumn();
}


/**
 * @param string $search
 * @return array<int, array<string, mixed>>
 */
public function searchCompany(string $search): array
{
    $sql = "SELECT *
            FROM {$this->tableName}
            WHERE 
                name LIKE :search 
            OR 
                slug LIKE :slug
            OR 
                ico LIKE :ico
            ORDER BY name";
    
    $stmt = $this->db()->prepare($sql);
    $stmt->execute([
        'search' => '%' . $search . '%',
        'slug' => '%' . $search . '%',
        'ico' => '%' . $search . '%'
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Najde jinou společnost se zadaným IČO.
 *
 * Aktuální společnost se z kontroly vylučuje.
 *
 * @param string $ico IČO společnosti
 * @param int $companyId ID aktuální společnosti
 * @return array<string, mixed>|null Data jiné společnosti nebo null
 */
public function findOtherCompanyByIco(string $ico, int $companyId): ?array
{
    return $this->fetchOne(
        "SELECT *
         FROM {$this->tableName}
         WHERE ico = :ico
           AND id <> :company_id
         LIMIT 1",
        [
            'ico' => $ico,
            'company_id' => $companyId,
        ]
    );
}

/**
 * Vrátí údaje aktuální společnosti potřebné pro fakturaci.
 *
 * Obsahuje základní údaje společnosti, údaje z company_details
 * a všechny aktivní bankovní účty.
 *
 * @param int $companyId ID společnosti
 * @return array<string, mixed>|null
 */
public function billingData(int $companyId): ?array
{
        $sql = "SELECT
                    c.id,
                    c.ico,
                    cd.official_name,
                    cd.dic,
                    cd.street,
                    cd.house_number,
                    cd.orientation_number,
                    cd.city_part,
                    cd.city,
                    cd.postal_code,
                    cd.country_code
                FROM {$this->tableName} c
                INNER JOIN company_details cd
                    ON cd.company_id = c.id
                WHERE c.id = :company_id
                LIMIT 1";
    $bankAccounts = [];

    try{
        // 1. Údaje společnosti + company_details

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
        ]);

        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($supplier === false) {
            return null;
        }

        // 2. Aktivní bankovní účty
        $bankAccounts = (new CompanyBankAccountModel())->activeForCompany();

        

        return [
            'id' => (int) $supplier['id'],
            'official_name' => (string) $supplier['official_name'],
            'ico' => (string) $supplier['ico'],
            'dic' => (string) ($supplier['dic'] ?? ''),
            'street' => (string) ($supplier['street'] ?? ''),
            'house_number' => (string) ($supplier['house_number'] ?? ''),
            'orientation_number' => (string) ($supplier['orientation_number'] ?? ''),
            'city_part' => (string) ($supplier['city_part'] ?? ''),
            'city' => (string) ($supplier['city'] ?? ''),
            'postal_code' => (string) ($supplier['postal_code'] ?? ''),
            'country_code' => (string) ($supplier['country_code'] ?? 'CZ'),
            'bank_accounts' => $bankAccounts,
        ];
    
        } catch (Throwable $e) {
            LoggerHolder::get()->error(
                'CompanyModel.billingData FAILED',
                [
                    'company_id' => $companyId,
                    'message'    => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                    'sql'        => $sql,
                    
                ]
            );

            return null;
        }


}
}