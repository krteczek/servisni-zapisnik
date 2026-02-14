<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Model pro práci s přiřazením úkolů (task_assignments) v tenant databázi.
 * Poskytuje agregační statistiky pro úkoly a pracovní příkazy.
 *
 * Tabulka uchovává informace o tom, kdo, kdy a s jakými náklady
 * (čas, kilometry) úkol plnil.
 */
final class AssignmentModel extends BaseModel
{
    /**
     * Název tabulky bez prefixu.
     *
     * @var string
     */
    protected string $table = 'task_assignments';

    /**
     * Připojení k tenant (work) databázi.
     *
     * @var string
     */
    protected string $connection = 'work';

    /**
     * Vrátí agregované statistiky pro zadané ID úkolů.
     * Výstup je klíčovaný podle task_id pro snadné napojení.
     *
     * Očekává:
     * - Pole neprázdných celých čísel
     * - Platná ID existujících úkolů (jinak vrací prázdné pole)
     *
     * TODO: [PERFORMANCE] Při velkém počtu taskIds (>100) zvážit rozdělení do batchů
     * TODO: [FEATURE] Přidat možnost časového filtru (např. poslední měsíc)
     *
     * @param array $taskIds Pole ID úkolů
     * @return array Associativní pole [task_id => ['total' => int, 'kilometers' => int, 'minutes' => int]]
     */
    public function statsForTasks(array $taskIds): array
    {
        if ($taskIds === []) {
            return [];
        }

        $taskIds = array_map('intval', $taskIds);

        $in = implode(',', array_fill(0, count($taskIds), '?'));

        $sql = "
            SELECT
                task_id,
                COUNT(*)                          AS total,
                COALESCE(SUM(kilometers), 0)      AS kilometers,
                COALESCE(SUM(minutes_spent), 0)   AS minutes_spent
            FROM {$this->tableName}
            WHERE task_id IN ($in)
              AND {$this->tenantColumn} = ?
            GROUP BY task_id
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([...$taskIds, $this->tenantId()]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['task_id']] = [
                'total'      => (int) $row['total'],
                'kilometers' => (int) $row['kilometers'],
                'minutes'    => (int) $row['minutes_spent'],
            ];
        }

        return $out;
    }

    /**
     * Vrátí souhrnné statistiky pro jeden pracovní příkaz.
     * Zahrnuje počet úkolů, přiřazení, najeté kilometry a strávený čas.
     *
     * Očekává:
     * - Platné ID existujícího pracovního příkazu
     *
     * TODO: [PERFORMANCE] Přidat index na (work_order_id, company_id)
     * TODO: [FEATURE] Přidat průměrné hodnoty (avg minutes per task)
     *
     * @param int $orderId ID pracovního příkazu
     * @return array Statistiky ve formátu:
     *               ['tasks' => int, 'assignments' => int, 'kilometers' => int, 'minutes' => int]
     */
    public function statsForWorkOrder(int $orderId): array
    {
        $sql = "
            SELECT
                COUNT(DISTINCT task_id)           AS tasks,
                COUNT(*)                          AS assignments,
                COALESCE(SUM(kilometers), 0)      AS kilometers,
                COALESCE(SUM(minutes_spent), 0)   AS minutes
            FROM {$this->tableName}
            WHERE work_order_id = :order
              AND {$this->tenantColumn} = :tenant
        ";

        $row = $this->fetchOne($sql, [
            'order'  => $orderId,
            'tenant' => $this->tenantId(),
        ]);

        return [
            'tasks'       => (int) ($row['tasks'] ?? 0),
            'assignments' => (int) ($row['assignments'] ?? 0),
            'kilometers'  => (int) ($row['kilometers'] ?? 0),
            'minutes'     => (int) ($row['minutes'] ?? 0),
        ];
    }
}