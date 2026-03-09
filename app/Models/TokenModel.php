<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;

/**
 * Model pro správu autentizačních tokenů (aktivace účtu, reset hesla).
 * Tokeny jsou uloženy v admin databázi a nejsou vázány na konkrétního tenanta.
 *
 * Podporuje:
 * - Vytvoření tokenu
 * - Validaci neexpirovaných a nepoužitých tokenů
 * - Zneplatnění všech tokenů uživatele (např. při novém požadavku)
 * - Čištění expirovaných tokenů
 */
final class TokenModel extends BaseModel
{
    /**
     * Název tabulky bez prefixu.
     *
     * @var string
     */
    protected string $table = 'tokens';

    /**
     * Připojení k admin databázi (centrální token store pro všechny tenanty).
     *
     * @var string
     */
    protected string $connection = 'admin';
    
    protected bool $tenantAware = false;

    /* ==========================================================
     * TRANSACTIONS
     * ========================================================== */

    /**
     * Zahájí databázovou transakci.
     * Používá se pro hromadné operace s tokeny (např. invalidace + vytvoření).
     *
     * Vedlejší efekty:
     * - Nastaví DB připojení do transakčního režimu
     *
     * TODO: [MAINTENANCE] Přesunout transakční metody do BaseModel
     *
     * @return void
     */
    public function begin(): void
    {
        if (!$this->db()->inTransaction()) {
            $this->db()->beginTransaction();
        }
    }

    /**
     * Potvrdí probíhající transakci.
     *
     * @return void
     */
    public function commit(): void
    {
        if ($this->db()->inTransaction()) {
            $this->db()->commit();
        }
    }

    /**
     * Zruší probíhající transakci.
     *
     * @return void
     */
    public function rollback(): void
    {
        if ($this->db()->inTransaction()) {
            $this->db()->rollBack();
        }
    }

    /* ==========================================================
     * FIND VALID TOKEN
     * ========================================================== */

    /**
     * Najde platný token podle hash hodnoty a typu.
     *
     * Token je považován za platný, pokud:
     * - Existuje v databázi
     * - Odpovídá zadanému typu (activation, password_reset)
     * - Není označen jako použitý (used_at IS NULL)
     * - Nevypršela jeho platnost (expires_at > NOW())
     * 
     *
     * TODO: [SECURITY] Přidat logování pokusů o neplatný token (prevence brute force)
     * TODO: [PERFORMANCE] Index na (token_hash, type, used_at, expires_at, company_id)
     *
     * @param string $hash Hashovaná hodnota tokenu (z URL parametru)
     * @param string $type Typ tokenu ('activation', 'password_reset')
     * @return array|null Data tokenu nebo null pokud není nalezen
     */
    public function findValidByHash(string $hash, string $type): ?array
    {
    	
        $sql = "
            SELECT id, user_id, email
            FROM {$this->tableName}
            WHERE token_hash = :hash
              AND type = :type
              
              AND used_at IS NULL
              AND expires_at > NOW()
              AND invalidated_at IS NULL
              
            LIMIT 1
        ";

         $out = $this->fetchOne($sql, [
            'hash'   => $hash,
            'type'   => $type,
        ]);
        
        return $out;
    }


public function findValidByHashForUpdate(string $hash, string $type): ?array
{
	 if (!$this->db()->inTransaction()) {
    throw new RuntimeException('Token consume requires transaction');
	}
    $sql = "
        SELECT id, user_id, email
        FROM {$this->tableName}
        WHERE token_hash = :hash
          AND type = :type
          AND used_at IS NULL
          AND expires_at > NOW()
          AND invalidated_at IS NULL
        LIMIT 1
        FOR UPDATE
    ";

    return $this->fetchOne($sql, [
        'hash' => $hash,
        'type' => $type,
    ]);
}


    /* ==========================================================
     * INVALIDATE OLD TOKENS
     * ========================================================== */

    /**
     * Zneplatní všechny dosud nepoužité tokeny daného uživatele a typu.
     *
     * Použití:
     * - Před vytvořením nového aktivačního tokenu
     * - Před vytvořením nového tokenu pro reset hesla
     * - Zajišťuje, že uživatel má vždy jen jeden platný token
     *
     * Vedlejší efekty:
     * - Nastaví used_at na aktuální čas pro všechny odpovídající tokeny
     *
     * TODO: [AUDIT] Logovat hromadné zneplatnění tokenů
     *
     * @param int $userId ID uživatele
     * @param string $type Typ tokenu
     * @return int Počet zneplatněných tokenů
     */

     public function invalidateActive(string $email, string $type): int
{
    $sql = "
        UPDATE {$this->tableName}
        SET invalidated_at = NOW()
        WHERE email = :email
          AND type = :type
          AND used_at IS NULL
          AND expires_at > NOW()
          AND invalidated_at IS NULL
    ";

    $stmt = $this->db()->prepare($sql);

    $stmt->execute([
        'email' => $email,
        'type'  => $type,
    ]);

    return $stmt->rowCount();
}

    /* ==========================================================
     * MARK AS USED
     * ========================================================== */

    /**
     * Označí konkrétní token jako použitý.
     * Volá se po úspěšném použití tokenu (aktivace účtu, změna hesla).
     *
     * Token je jednorázový – po použití již není platný.
     *
     * @param int $id ID tokenu
     * @return bool TRUE pokud byl token aktualizován
     */
    public function markUsed(int $id): bool
    {

    		$sql = "UPDATE {$this->tableName}
SET used_at = NOW()
WHERE id = :id
  AND used_at IS NULL
  AND invalidated_at IS NULL
  LIMIT 1
  ";
        $stmt = $this->db()->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->rowCount() === 1;
    }


   
    /* ==========================================================
     * HOUSEKEEPING
     * ========================================================== */

    /**
     * Smaže expirované a již použité tokeny.
     *
     * Podmínky pro smazání:
     * - Token je expirovaný (expires_at < NOW())
     * - A zároveň je již použitý (used_at IS NOT NULL)
     *
     * Nepoužité, ale expirované tokeny NEMAŽEME – uchováváme pro audit.
     *
     * TODO: [PERFORMANCE] Přidat index na (expires_at, used_at)
     * TODO: [FEATURE] Přidat konfigurovatelnou dobu uchovávání (např. 30 dní)
     *
     * @return int Počet smazaných tokenů
     */
    public function deleteExpired(): int
    {
        $sql = "
DELETE FROM {$this->tableName}
WHERE expires_at < NOW() - INTERVAL 30 DAY
AND (
    used_at IS NOT NULL
    OR invalidated_at IS NOT NULL
)              
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Vytvoří nový token.
     * Wrapper pro BaseModel::create() s explicitním názvem.
     *
     * Očekává:
     * - $data obsahuje: user_id, token_hash, type, expires_at
     * - tenant_id (company_id) je doplněno automaticky metodou create()
     *
     * @param array $data Data tokenu
     * @return int ID nově vytvořeného tokenu
     */
    public function createToken(array $data): int
    {
        return $this->create($data);
    }
}