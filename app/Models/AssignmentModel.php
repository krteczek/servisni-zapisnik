<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
class AssignmentModel extends TenantModel
{
    protected string $table = 'task_assignments';
    protected string $connection = 'work';

    public function statsForTasks(array $taskIds): array
    {
        if ($taskIds === []) {
            return [];
        }

        $in = implode(',', array_fill(0, count($taskIds), '?'));

        $sql = "
            SELECT
                task_id,
                SUM(status = 'open')      AS open,
                SUM(status = 'done')      AS done,
                SUM(status = 'cancelled') AS cancelled,
                COUNT(*)                  AS total
            FROM {$this->tableName}
            WHERE task_id IN ($in)
              AND {$this->tenantColumn()} = ?
            GROUP BY task_id
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([...$taskIds, $this->tenantId()]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $out[(int)$row['task_id']] = [
                'open'      => (int)$row['open'],
                'done'      => (int)$row['done'],
                'cancelled' => (int)$row['cancelled'],
                'total'     => (int)$row['total'],
            ];
        }

        return $out;
    }
}
