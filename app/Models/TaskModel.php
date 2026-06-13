<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;
use App\Core\Auth;
use App\Core\Roles;

final class TaskModel extends BaseModel
{
    /**
     * Název tabulky bez prefixu.
     *
     * @var string
     */
    protected string $table = 'tasks';

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
    
    protected bool $tenantAware = true;

    /**
     * Najde úkol podle ID (tenant-aware přes BaseModel)
     */
    public function findById(int $taskId): ?array
    {
        return $this->find($taskId);
    }
    /**
     * Obecná validační metoda
     */
public function canBeClosed(array $task, string $newStatus): bool
{
    if ($task['status'] !== 'open') {
        return false;
    }

    $statsMap = $this->statsForTasks([(int)$task['id']]);
    $stats = $statsMap[(int)$task['id']];

    return match ($newStatus) {
        'done'      => $this->canBeDone($stats),
        'cancelled' => $this->canBeCancelled($stats),
        default     => false,
    };
}

private function canBeDone(array $stats): bool
{
    return $stats['assignments_count'] > 0;
}

private function canBeCancelled(array $stats): bool
{
    return $stats['assignments_count'] === 0;
}


    public function closeRecurringTask(int $taskId, string $newStatus = 'done'): bool
    {        
        $task = $this->findById($taskId);

        if (!$task) {
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
 

/**
 * Upravený forIndex,
 */

public function forIndex(?int $filterUserId = null): array
{
    $companyId    = Auth::companyId();
    $userId       = Auth::id();
    $role         = Auth::role();
    $isManagement = Roles::isManagement($role);

    $params = [
        'company_id_t'         => $companyId,
        'company_id_w'         => $companyId,
        'company_id_tm'        => $companyId,
        'company_id_ta'        => $companyId,
        'company_id_perm'      => $companyId,
        'current_user_id_perm' => $userId,
        'status'               => 'open',
    ];


$sql =
    "SELECT
        t.*,

        -- zakázka
        w.title    AS work_order_title,
        w.status   AS work_order_status,
        w.priority AS work_order_priority,

        -- tým
        tm.name  AS team_name,
        tm.color AS team_color,

        -- statistiky
        COALESCE(r.reports_count, 0) AS reports_count,
        COALESCE(r.total_minutes, 0) AS total_minutes,
        COALESCE(r.total_km, 0)      AS total_km,

        -- oprávnění na report
        CASE
            WHEN t.status = 'open'
                 AND EXISTS (
                    SELECT 1
                    FROM team_memberships tmu2
                    WHERE tmu2.team_id = t.team_id
                      AND tmu2.user_id = :current_user_id_perm
                      AND tmu2.company_id = :company_id_perm
                      AND (
                            tmu2.valid_to IS NULL
                            OR tmu2.valid_to >= CURDATE()
                          )
                 )
            THEN 1
            ELSE 0
        END AS can_add_report

    FROM tasks t

    LEFT JOIN work_orders w
        ON w.id = t.work_order_id
        AND w.company_id = :company_id_w

    LEFT JOIN teams tm
        ON tm.id = t.team_id
        AND tm.company_id = :company_id_tm

    LEFT JOIN (
        SELECT
            ta.task_id,
            COUNT(*) AS reports_count,
            COALESCE(SUM(ta.kilometers), 0)    AS total_km,
            COALESCE(SUM(ta.minutes_spent), 0) AS total_minutes
        FROM task_assignments ta
        WHERE ta.company_id = :company_id_ta
        GROUP BY ta.task_id
    ) r ON r.task_id = t.id

    WHERE
        t.company_id = :company_id_t
        AND t.status = :status
        AND t.task_type <> 'recurring_master'
";

    /*
     * MANAGEMENT filtr (jen filtruje, neuděluje oprávnění)
     */
    if ($isManagement) {

        if ($filterUserId !== null) {
            $sql .= " AND t.created_by_user_id = :filter_user_id";
            $params['filter_user_id'] = $filterUserId;
        }

        $sql .= "
            ORDER BY
                (t.created_by_user_id = :current_user_id_sort) DESC,
                t.id DESC
        ";

        $params['current_user_id_sort'] = $userId;

    } else {

        $sql .= "
            AND EXISTS (
                SELECT 1
                FROM team_memberships tmu
                WHERE tmu.team_id = t.team_id
                  AND tmu.user_id = :current_user_id_filter
                  AND tmu.company_id = :company_id_filter
                  AND (tmu.valid_to IS NULL OR tmu.valid_to >= CURDATE())
            )
            ORDER BY t.id DESC
        ";

        $params['current_user_id_filter'] = $userId;
        $params['company_id_filter']      = $companyId;
    }

    $tasks = $this->fetchAll($sql, $params);

    foreach ($tasks as &$task) {
        $task['total_minutes'] = (int)$task['total_minutes'];

        $task['total_hours_formatted'] =
            floor($task['total_minutes'] / 60) . 'h ' .
            ($task['total_minutes'] % 60) . 'm';

        $task['can_add_report'] = (bool)$task['can_add_report'];
    }

    return $tasks;
}


public function canUserAddReport(int $taskId, int $userId): bool
{
    $companyId = Auth::companyId();

    $sql = "
        SELECT 1
        FROM tasks t
        WHERE t.id = :task_id
          AND t.company_id = :company_id
          AND t.status = 'open'
          AND EXISTS (
                SELECT 1
                FROM team_memberships tmu
                WHERE tmu.team_id = t.team_id
                  AND tmu.user_id = :user_id
                  AND tmu.company_id = :company_id_membership
                  AND (tmu.valid_to IS NULL OR tmu.valid_to >= CURDATE())
          )
        LIMIT 1
    ";

    $params = [
        'task_id'               => $taskId,
        'company_id'            => $companyId,
        'user_id'               => $userId,
        'company_id_membership' => $companyId,
    ];

    $result = $this->fetchOne($sql, $params);

    return $result !== null;
}

public function forWorkOrderWithStats(int $orderId): array
{
    $sql = 
        "SELECT
            t.*,

            CASE
                WHEN rt_master.task_id IS NOT NULL THEN 1
                ELSE 0
            END AS is_recurring_master,

            CASE
                WHEN t.recurring_task_id IS NOT NULL THEN 1
                ELSE 0
            END AS is_generated_task,

            rt_master.id                  AS recurring_master_id,
            rt_master.frequency_type      AS recurring_frequency_type,
            rt_master.frequency_value     AS recurring_frequency_value,
            rt_master.next_due_date       AS recurring_next_due_date,
            rt_master.warning_days_before AS recurring_warning_days_before,
            rt_master.active              AS recurring_active

        FROM tasks t

        LEFT JOIN recurring_tasks rt_master
            ON rt_master.task_id = t.id
        AND rt_master.company_id = t.company_id

        WHERE 
            t.work_order_id = :order
            AND t.{$this->tenantColumn} = :tenant

        ORDER BY
            t.status IN ('done','cancelled'),
            t.created_at DESC
    ";

    $tasks = $this->fetchAll($sql, [
        'order'  => $orderId,
        'tenant' => $this->tenantId(),
    ]);

    if ($tasks === []) {
        return [];
    }

    $taskIds = array_column($tasks, 'id');

    $statsMap = $this->statsForTasks($taskIds);

    foreach ($tasks as $i => $task) {

        $stats = $statsMap[$task['id']] ?? [
            'assignments_count' => 0,
            'total_minutes'     => 0,
            'total_km'          => 0,
            'workers_count'     => 0,
        ];

        $tasks[$i]['stats'] = $stats;

        $tasks[$i]['can_cancel'] = $this->canBeCancelled($stats);

        $tasks[$i]['can_close'] = $this->canBeDone($stats);
    }

    return $tasks;
}

    public function statsForWorkOrder(int $orderId): array
    {
        $row = $this->fetchOne(
            "
            SELECT
                COUNT(*) AS total,
                SUM(status = 'open') AS open,
                SUM(status = 'done') AS done,
                SUM(status = 'cancelled') AS cancelled
            FROM {$this->tableName}
            WHERE work_order_id = :order_id
            AND {$this->tenantColumn} = :company_id
            ",
            [
                'order_id'  => $orderId,
                'company_id'=> $this->tenantId(),
            ]
        );

        return [
            'total'     => (int) ($row['total'] ?? 0),
            'open'      => (int) ($row['open'] ?? 0),
            'done'      => (int) ($row['done'] ?? 0),
            'cancelled' => (int) ($row['cancelled'] ?? 0),
        ];
    }

    public function statsForTasks(array $taskIds): array
    {
        if (empty($taskIds)) {
            return [];
        }

        $placeholders = [];
        $params = ['company_id' => $this->tenantId()];

        foreach ($taskIds as $i => $id) {
            $key = "task_$i";
            $placeholders[] = ":$key";
            $params[$key] = (int) $id;
        }

$sql = 
    "SELECT
        ta.task_id,

        COUNT(DISTINCT ta.id) AS assignments_count,

        COALESCE(SUM(ta.kilometers), 0) AS total_km,

        COALESCE(SUM(ta.minutes_spent), 0) AS total_minutes,

        (
            SELECT COUNT(DISTINCT tap.user_id)
            FROM task_assignment_participants tap

            INNER JOIN task_assignments ta2
                ON ta2.id = tap.assignment_id

            WHERE ta2.task_id = ta.task_id
              AND tap.company_id = ta.company_id
        ) AS workers_count

    FROM task_assignments ta

    WHERE ta.task_id IN (" . implode(',', $placeholders) . ")
      AND ta.company_id = :company_id

    GROUP BY ta.task_id
";       

        $rows = $this->fetchAll($sql, $params);

        $result = [];

        foreach ($taskIds as $taskId) {
            $result[$taskId] = [
                'assignments_count' => 0,
                'total_minutes'     => 0,
                'total_km'          => 0,
                'workers_count'     => 0,
            ];
        }

        foreach ($rows as $row) {
            $result[(int)$row['task_id']] = [
                'assignments_count' => (int)$row['assignments_count'],
                'total_minutes'     => (int)$row['total_minutes'],
                'total_km'          => (int)$row['total_km'],
                'workers_count'     => (int)$row['workers_count'],
            ];
        }

        return $result;
    }  
    /*
    * Exporty
    */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE id IN ($placeholders)
            AND {$this->tenantColumn} = ?
        ";

        $params = [...$ids, $this->tenantId()];

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function filter(array $filters): array
    {
        $sql = "SELECT t.*, tm.name AS team_name
                FROM {$this->tableName} t
                LEFT JOIN teams tm ON tm.id = t.team_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['date'])) {
            $sql .= " AND DATE(t.created_at) = :date";
            $params['date'] = $filters['date'];
        }

        if (!empty($filters['team_id'])) {
            $sql .= " AND t.team_id = :team_id";
            $params['team_id'] = (int)$filters['team_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " AND t.{$this->tenantColumn} = :company_id";
        $params['company_id'] = $this->tenantId();

        $sql .= " ORDER BY t.id DESC";

        $out = $this->fetchAll($sql, $params);

        return $out;
    }

    public function filterArchive(array $filters): array
    {
        $sql = 
            "SELECT t.*, 
                w.title AS work_order_title, 
                w.status AS work_order_status,
                tm.name AS team_name,
                tm.color AS team_color

            FROM {$this->tableName} t

            LEFT JOIN teams tm 
                ON tm.id = t.team_id
                AND tm.company_id = t.company_id

            INNER JOIN work_orders w 
                ON w.id = t.work_order_id
                AND w.company_id = t.company_id
        ";
        $sql .= " WHERE t.company_id = ?";
        $params = [$this->tenantId()];

        // status
        if ($filters['status'] !== 'all') {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        } else {
            $sql .= " AND t.status IN ('done','cancelled')";
        }

        // search
        if ($filters['q'] !== '') {
            $sql .= " AND t.title LIKE ?";
            $params[] = '%' . $filters['q'] . '%';
        }

        // team
        if (!empty($filters['team_id'])) {
            $sql .= " AND t.team_id = ?";
            $params[] = $filters['team_id'];
        }

        $sql .= " ORDER BY t.created_at DESC";

        return $this->fetchAll($sql, $params);
    }
}
