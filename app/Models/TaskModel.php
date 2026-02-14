<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;

final class TaskModel extends BaseModel
{
    protected string $table = 'tasks';
    protected string $connection = 'work';
    protected bool $tenantAware = true;

    /**
     * Najde úkol podle ID (tenant-aware přes BaseModel)
     */
    public function findById(int $taskId): ?array
    {
        return $this->findRow($taskId);
    }

    /**
     * Vrátí statistiky k úkolu potřebné pro validaci
     */
    public function getTaskStats(int $taskId): array
    {
        return [
            'assignments_count' => (int) $this->fetchValue(
                "SELECT COUNT(*) 
                 FROM task_assignments 
                 WHERE task_id = :task_id 
                   AND company_id = :company_id",
                [
                    'task_id' => $taskId,
                    'company_id' => $this->tenantId()
                ]
            ),
        ];
    }

    /**
     * Obecná validační metoda
     */
    public function canBeClosed(array $task, string $newStatus): bool
    {
        if ($task['status'] !== 'open') {
            return false;
        }

        $stats = $this->getTaskStats((int)$task['id']);

        return match ($newStatus) {
            'done'      => $this->canBeDone($stats),
            'cancelled' => $this->canBeCancelled($stats),
            default     => false,
        };
    }

    private function canBeDone(array $stats): bool
    {
        // Příklad logiky:
        // Úkol může být hotový jen pokud existuje alespoň jedno plnění
        return $stats['assignments_count'] > 0;
    }

    private function canBeCancelled(array $stats): bool
    {
        // Například: můžeš zrušit jen pokud nemá žádné plnění
        return $stats['assignments_count'] === 0;
    }

    /**
     * Uzavře úkol změnou statusu
     */
    public function closeTask(int $taskId, string $newStatus): bool
    {
        $task = $this->findById($taskId);

        if (!$task) {
            return false;
        }

        if (!$this->canBeClosed($task, $newStatus)) {
            return false;
        }

        $data = [
            'status' => $newStatus,
        ];

        if ($newStatus === 'done') {
            $data['done_at'] = (new DateTime())->format('Y-m-d H:i:s');
        }

        if ($newStatus === 'cancelled') {
            $data['done_at'] = null;
        }

        return $this->update($taskId, $data);
    }

    /**
     * Vrátí všechny otevřené úkoly pro tým
     */
    public function getOpenTasksByTeam(int $teamId): array
    {
        return $this->fetchAll(
            "SELECT *
             FROM {$this->tableName}
             WHERE team_id = :team_id
               AND status = 'open'
               AND company_id = :company_id
             ORDER BY created_at DESC",
            [
                'team_id' => $teamId,
                'company_id' => $this->tenantId()
            ]
        );
    }
    
    public function forIndex(): array
{
    $sql = "
        SELECT
            t.id,
            t.title,
            t.status,
            t.team_id,
            t.description,
            COUNT(ta.id) AS reports_count,
            COALESCE(SUM(ta.minutes_spent), 0) AS minutes_spent,
            COALESCE(SUM(ta.kilometers), 0) AS kilometers
        FROM {$this->tableName} t
        LEFT JOIN task_assignments ta
            ON ta.task_id = t.id
           AND ta.company_id = t.company_id
        WHERE t.company_id = :company_id
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ";

    return $this->fetchAll($sql, [
        'company_id' => $this->tenantId()
    ]);
}

    public function forWorkOrderWithStats(int $orderId): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE work_order_id = :order
              AND {$this->tenantColumn} = :tenant
            ORDER BY
                status IN ('done','cancelled'),  -- otevřené nahoře
                created_at DESC
        ";

        $tasks = $this->fetchAll($sql, [
            'order'  => $orderId,
            'tenant'=> $this->tenantId(),
        ]);

        if ($tasks === []) {
            return [];
        }

        $taskIds = array_column($tasks, 'id');

        $assignmentModel = new AssignmentModel();
        $statsMap = $assignmentModel->statsForTasks($taskIds);

        foreach ($tasks as $i => $task) {
            $stats = $statsMap[$task['id']] ?? [
    'total'      => 0,
    'kilometers' => 0,
    'minutes'    => 0,
];

            $tasks[$i]['stats'] = $stats;
            $tasks[$i]['can_cancel'] = $this->canBeCancelled($task, $stats);
            $tasks[$i]['can_close']  = $this->canBeDone($task, $stats);
        }

        return $tasks;
    }
    
}
