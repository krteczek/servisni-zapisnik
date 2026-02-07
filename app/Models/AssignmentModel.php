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
            COUNT(*)                    AS total,
            SUM(kilometers)             AS kilometers,
            SUM(minutes_spent)          AS minutes_spent
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
        $out[(int) $row['task_id']] = [
            'total'        => (int) $row['total'],
            'kilometers'   => (int) $row['kilometers'],
            'minutes'      => (int) $row['minutes_spent'],
        ];
    }

    return $out;
}

public function statsForWorkOrder(int $orderId): array
{
    $sql = "
SELECT
    COUNT(DISTINCT task_id) AS tasks,
    COUNT(*)                AS assignments,
    SUM(kilometers)         AS kilometers,
    SUM(minutes_spent)      AS minutes
FROM {$this->tableName}
WHERE work_order_id = :order
  AND {$this->tenantColumn()} = :tenant
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
