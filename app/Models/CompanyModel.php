<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

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
     * @return array|null Data společnosti nebo null
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
     * @return array|null Data společnosti nebo null
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            "SELECT *
             FROM {$this->tableName}
             WHERE slug = :slug
               AND active = 1
             LIMIT 1",
            ['slug' => $slug]
        );
    }

    /**
     * Vrátí seznam všech společností se základními informacemi.
     * Používá se v administraci pro přehled tenantů.
     *
     * TODO: [PERFORMANCE] Přidat stránkování při velkém počtu tenantů
     * TODO: [FEATURE] Přidat filtrování (active/inactive, datum vytvoření)
     *
     * @return array Seznam společností (id, name, slug, created_at)
     */
    public function findAll(): array
    {
        return $this->fetchAll(
            "SELECT id, name, slug, created_at
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
    $sql = "SELECT 1 FROM companies WHERE ico = :ico LIMIT 1";
    
    $stmt = $this->db()->prepare($sql);
    $stmt->execute(['ico' => $ico]);
    
    return (bool) $stmt->fetchColumn();
}
}