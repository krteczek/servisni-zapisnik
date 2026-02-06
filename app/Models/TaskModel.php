<?php
declare(strict_types=1);

namespace App\Models;

use LogicException;

class TaskModel extends TenantModel
{
    protected string $connection = 'work';
    protected string $table = 'tasks';

    /* =========================
       SELECTY
       ========================= */

    public function byWorkOrder(int $orderId): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE work_order_id = :order
              AND company_id = :tenant
            ORDER BY id DESC
        ";

        return $this->fetchAll($sql, [
            'order'  => $orderId,
            'tenant' => $this->tenantId(),
        ]);
    }

    public function unassigned(): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE work_order_id IS NULL
              AND company_id = :tenant
            ORDER BY id DESC
        ";

        return $this->fetchAll($sql, [
            'tenant' => $this->tenantId(),
        ]);
    }

    /* =========================
       DOMÉNOVÉ OPERACE
       ========================= */

    public function markDone(int $taskId): bool
    {
        return $this->update($taskId, [
            'status'  => 'done',
            'done_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function cancel(int $taskId): bool
    {
        return $this->update($taskId, [
            'status' => 'cancelled',
        ]);
    }

    public function assignToOrder(int $taskId, int $orderId): bool
    {
        return $this->update($taskId, [
            'work_order_id' => $orderId,
        ]);
    }
    
    public function countByWorkOrder(int $orderId): int
{
    $sql = "
        SELECT COUNT(*) AS cnt
        FROM {$this->tableName}
        WHERE work_order_id = :order
          AND company_id = :tenant
    ";

    $row = $this->fetchOne($sql, [
        'order'  => $orderId,
        'tenant' => $this->tenantId(),
    ]);

    return (int) ($row['cnt'] ?? 0);
}

public function allDoneByWorkOrder(int $orderId): bool
{
    $sql = "
        SELECT COUNT(*) AS open_cnt
        FROM {$this->tableName}
        WHERE work_order_id = :order
          AND company_id = :tenant
          AND status != 'done'
    ";

    $row = $this->fetchOne($sql, [
        'order'  => $orderId,
        'tenant' => $this->tenantId(),
    ]);

    return ((int) ($row['open_cnt'] ?? 0)) === 0;
}
   

    public function statsForWorkOrderOLD(int $workOrderId): array
    {
        $sql = "
            SELECT status, COUNT(*) AS cnt
            FROM tasks
            WHERE company_id = :company
              AND work_order_id = :wo
            GROUP BY status
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'company' => $this->tenantId(),
            'wo'      => $workOrderId,
        ]);

        $stats = [
            'open'      => 0,
            'done'      => 0,
            'canceled'  => 0,
            'total'     => 0,
        ];

        foreach ($stmt->fetchAll() as $row) {
            $stats[$row['status']] = (int) $row['cnt'];
            $stats['total'] += (int) $row['cnt'];
        }

        return $stats;
    }

public function statsForWorkOrder(int $workOrderId): array
{
    $sql = "
        SELECT status, COUNT(*) AS cnt
        FROM {$this->tableName}
        WHERE company_id = :company
          AND work_order_id = :wo
        GROUP BY status
    ";

    $rows = $this->fetchAll($sql, [
        'company' => $this->tenantId(),
        'wo'      => $workOrderId,
    ]);

    $stats = [
        'open'      => 0,
        'done'      => 0,
        'cancelled'=> 0,
        'total'     => 0,
    ];

    foreach ($rows as $row) {
        $stats[$row['status']] = (int) $row['cnt'];
        $stats['total'] += (int) $row['cnt'];
    }

    return $stats;
}
    public function forWorkOrderWithStats(int $orderId): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE work_order_id = :order
              AND {$this->tenantColumn()} = :tenant
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
                'open' => 0, 'done' => 0, 'cancelled' => 0, 'total' => 0
            ];

            $tasks[$i]['stats'] = $stats;
            $tasks[$i]['can_cancel'] = $this->canBeCancelled($stats);
            $tasks[$i]['can_close']  = $this->canBeDone($stats);
        }

        return $tasks;
    }

    private function canBeCancelled(array $stats): bool
    {
        return $stats['total'] === 0;
    }

    private function canBeDone(array $stats): bool
    {
        return $stats['open'] === 0 && $stats['done'] > 0;
    }
}
