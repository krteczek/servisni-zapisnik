<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Model pro práci s tabulkou access_logs.
 * Slouží pro logování přístupů (403, 404, přihlášení, odhlášení)
 * a detekci podezřelé aktivity (brute force, skenování).
 */
final class AccessLogModel extends BaseModel
{
    /**
     * Název tabulky bez prefixu.
     *
     * @var string
     */
    protected string $table = 'access_logs';
    protected string $connection = 'admin';
    protected bool $tenantAware = false;


    /**
     * Vytvoří nový záznam v access logu.
     *
     * company_id a user_id mohou být NULL, protože access log
     * zaznamenává i požadavky nepřihlášených návštěvníků.
     *
     * company_id je pouze kontextová informace, nikoliv tenant filtr.
     *
     *
     * Očekává:
     * - Pole $data obsahuje klíče: company_id, user_id, ip_address, type, path, method, user_agent
     * - user_id může být null (nepřihlášený uživatel)
     *
     * TODO: [PERFORMANCE] Při vysokém provozu zvážit batch insert nebo async logging
     * TODO: [SECURITY] Sanitizace path a user_agent před uložením
     *
     * @param array<string, mixed> $data Data z AccessLogger::log()
     * @return void
     */
    public function log(array $data): void
    {
        $this->create([
            'company_id' => $data['company_id'] ?? null,
            'user_id'    => $data['user_id'] ?? null,
            'ip_address' => $data['ip_address'],
            'type'       => $data['type'],
            'path'       => $data['path'],
            'method'     => $data['method'],
            'user_agent' => $data['user_agent'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Spočítá počet záznamů daného typu z konkrétní IP adresy
     * za posledních N minut.
     *
     * Používá se pro:
     * - Detekci brute force útoků (403, 404)
     * - Rate limiting
     * - Bezpečnostní monitoring
     *
     * TODO: [PERFORMANCE] Přidat index na (type, ip_address, created_at)
     * TODO: [FEATURE] Přidat variantu pro counting podle user_id
     *
     * @param int $type Typ události (403, 404, atd.)
     * @param int $minutes Časový interval v minutách
     * @return int Počet záznamů
     */
    public function countRecent(
        int $type,
        string $ip,
        int $minutes
    ): int {
        $sql = "
            SELECT COUNT(*)
            FROM {$this->tableName}
            WHERE type = :type
            AND ip_address = :ip
            AND created_at >= DATE_SUB(NOW(), INTERVAL :min MINUTE)
        ";

        $stmt = $this->db()->prepare($sql);

        $stmt->bindValue(':type', $type, PDO::PARAM_INT);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':min', $minutes, PDO::PARAM_INT);

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
   

    public function countRecentOLD(
        int $type,
        int $userId,
        int $minutes
    ): int {
        $sql = "
            SELECT COUNT(*)
            FROM {$this->tableName}
            WHERE type = :type
              AND user_id = :userId
              AND created_at >= DATE_SUB(NOW(), INTERVAL :min MINUTE)
              AND {$this->tenantColumn} = :tenant
        ";

        $stmt = $this->db()->prepare($sql);

        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':min', $minutes, PDO::PARAM_INT);
        $stmt->bindValue(':tenant', $this->tenantId(), PDO::PARAM_INT);

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}