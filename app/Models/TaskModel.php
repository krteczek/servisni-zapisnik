<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;
use App\Core\Auth;
use App\Core\Roles;
use App\Services\Tasks\TaskType;
use App\Services\Tasks\TaskStatus;

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
     * Model JE tenant-aware
     *
     * @var bool
     */    
    protected bool $tenantAware = true;

    /**
     * Najde úkol podle ID (tenant-aware přes BaseModel)
     *
     * @param int $taskId
     * @return array<string, mixed>|null
     */
    public function findById(int $taskId): ?array
    {
        return $this->find($taskId);
    }

    /**
     * Obecná validační metoda
     * Lze uzavřít task? 
     * 1. jen tehdy, když status je open
     * 2. jestli má nějaké reporty tak done
     * 3. pokud nemá report, cancelled
     * @param array<string, mixed> $task
     * @param string $newStatus
     * @return bool
     */
    public function canBeClosed(array $task, string $newStatus): bool
    {
        if (TaskStatus::isClosed($task['status'])) {
            return false;
        }

        $statsMap = $this->statsForTasks([(int)$task['id']]);
        $stats = $statsMap[(int)$task['id']];

        if (TaskStatus::isDone($newStatus)) {
            return $this->canBeDone($stats);
        }

        if (TaskStatus::isCancelled($newStatus)) {
            return $this->canBeCancelled($stats);
        }

        return false;
    }

    /**
     * @param array<string, mixed> $stats
     * @return bool
     */    
    private function canBeDone(array $stats): bool
    {
        return $stats['assignments_count'] > 0;
    }

    /**
     * @param array<string, mixed> $stats
     * @return bool
     */
    private function canBeCancelled(array $stats): bool
    {
        return $stats['assignments_count'] === 0;
    }

    /**
     * Uzavře opakující se úkol změnou statusu
     * @param int $taskId
     * @param string $newStatus
     * @return bool
     */
    public function closeRecurringTask(int $taskId, string $newStatus = TaskStatus::DONE): bool
    {        
        $task = $this->findById($taskId);

        if ($task === null) {
            return false;
        }
     
        $data = [
            'status' => $newStatus,
        ];

        if (TaskStatus::isDone($newStatus)) {
            $data['done_at'] = (new DateTime())->format('Y-m-d H:i:s');
        }

        if (TaskStatus::isCancelled($newStatus)) {
            $data['done_at'] = null;
        }

        return $this->update($taskId, $data);
    }
    /**
     * Uzavře úkol změnou statusu
     * @param int $taskId
     * @param string $newStatus
     * @return bool
     */
    public function closeTask(int $taskId, string $newStatus): bool
    {
        $task = $this->findById($taskId);

        if ($task === null) {
            return false;
        }
        if (!$this->canBeClosed($task, $newStatus)) {
            return false;
        }

        $data = [
            'status' => $newStatus,
        ];

        if (TaskStatus::isDone($newStatus)) {
            $data['done_at'] = (new DateTime())->format('Y-m-d H:i:s');
        }

        if (TaskStatus::isCancelled($newStatus)) {
            $data['done_at'] = null;
        }

        return $this->update($taskId, $data);
    }

    /**
     * Vrátí všechny otevřené úkoly pro tým
     * @param int $teamId
     * @return array<int, array<string, mixed>>
     */
    public function getOpenTasksByTeam(int $teamId): array
    {
        return $this->fetchAll(
            "SELECT *
             FROM {$this->tableName}
             WHERE team_id = :team_id
               AND status = :taskStatus
               AND company_id = :company_id
             ORDER BY created_at DESC",
            [
                'team_id' => $teamId,
                'taskStatus' => TaskStatus::OPEN,
                'company_id' => $this->tenantId()
            ]
        );
    }
 

    /**
     * Upravený forIndex, který bere v potaz, že management může filtrovat podle uživatele
     * @param int|null $filterUserId
     * @return array<int, array<string, mixed>>
     */
    public function forIndex(?int $filterUserId = null): array
    {
        $companyId    = Auth::companyId();
        $userId       = Auth::id();
        $role         = Auth::role();
        $isManagement = Roles::isManagement($role);

        $params = [
            'status_open_a'          => TaskStatus::OPEN,
            'company_id_t'         => $companyId,
            'company_id_w'         => $companyId,
            'company_id_tm'        => $companyId,
            'company_id_ta'        => $companyId,
            'company_id_perm'      => $companyId,
            'current_user_id_perm' => $userId,
            'status_open_b'        => TaskStatus::OPEN,
            'recurring_master'     => TaskType::RECURRING_MASTER
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
                WHEN t.status = :status_open_a
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
            AND t.status = :status_open_b
            AND t.task_type <> :recurring_master
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

    /**
     * @param int $taskId
     * @param int $userId
     * @return bool
     */
    public function canUserAddReport(int $taskId, int $userId): bool
    {
        $companyId = Auth::companyId();

        $sql = "
            SELECT 1
            FROM tasks t
            WHERE t.id = :task_id
                AND t.company_id = :company_id
                AND t.status = :taskStatus
                AND EXISTS (
                        SELECT 1
                        FROM team_memberships tmu
                        WHERE tmu.team_id = t.team_id
                        AND tmu.user_id = :user_id
                        AND tmu.company_id = :company_id_membership
                        AND tmu.valid_from <= CURDATE()
                        AND (tmu.valid_to IS NULL OR tmu.valid_to >= CURDATE())
                    )
                LIMIT 1
        ";

        $params = [
            'task_id'               => $taskId,
            'company_id'            => $companyId,
            'taskStatus'            => TaskStatus::OPEN,
            'user_id'               => $userId,
            'company_id_membership' => $companyId,
        ];

        $result = $this->fetchOne($sql, $params);

        return $result !== null;
    }

    /**
     * @param int $orderId
     * @return array<int, array<string, mixed>>
     */
    public function forWorkOrderWithStats(int $orderId): array
    {
        $sql = 
            "SELECT
                t.*,

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
                t.status IN (:taskStatusDone, :taskStatusCancelled),
                t.created_at DESC
        ";

        $tasks = $this->fetchAll($sql, [
            'order'                 => $orderId,
            'tenant'                => $this->tenantId(),
            'taskStatusDone'        => TaskStatus::DONE,
            'taskStatusCancelled'   => TaskStatus::CANCELLED
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

    /**
     * @param int $orderId
     * @return array<string, int>
     */
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

    /**
     * @param array<int, int> $taskIds
     * @return array<int, array<string, int>>
     */    
    public function statsForTasks(array $taskIds): array
    {
        if ($taskIds === []) {
            return [];
        }

        $placeholders = [];
        $params = ['company_id' => $this->tenantId()];

        foreach ($taskIds as $i => $id) {
            $key = "task_$i";
            $placeholders[] = ":$key";
            $params[$key] = $id;
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

    /**
     * Exporty
     * @param array<int, int> $ids
     * @return array<int, array<string, mixed>>
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

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function filter(array $filters): array
    {
        $sql = "SELECT t.*, tm.name AS team_name
                FROM {$this->tableName} t
                LEFT JOIN teams tm ON tm.id = t.team_id
                WHERE 1=1";

        $params = [];

        if ($filters['date'] !== '') {
            $sql .= " AND DATE(t.created_at) = :date";
            $params['date'] = $filters['date'];
        }

        if ($filters['team_id'] !== '') {
            $sql .= " AND t.team_id = :team_id";
            $params['team_id'] = (int)$filters['team_id'];
        }

        if ($filters['status'] !== '') {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " AND t.{$this->tenantColumn} = :company_id";
        $params['company_id'] = $this->tenantId();

        $sql .= " ORDER BY t.id DESC";

        $out = $this->fetchAll($sql, $params);

        return $out;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
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
            $sql .= " AND t.status IN (?, ?)";
            $params[] = TaskStatus::DONE;
            $params[] = TaskStatus::CANCELLED;

        }

        // search
        if ($filters['q'] !== '') {
            $sql .= " AND t.title LIKE ?";
            $params[] = '%' . $filters['q'] . '%';
        }

        // team
        if (($filters['team_id'] ?? '') !== '') {
            $sql .= " AND t.team_id = ?";
            $params[] = $filters['team_id'];
        }

        $sql .= " ORDER BY t.created_at DESC";

        return $this->fetchAll($sql, $params);
    }

    /**
     * @param int $recurringTaskId
     * @return int
     */
    public function countRecurringInstances(int $recurringTaskId): int
    {
        $sql = "
            SELECT COUNT(*) AS instance_count
            FROM tasks t
            WHERE t.recurring_task_id = :recurring_task_id
            AND t.task_type = :task_type
            AND t.{$this->tenantColumn} = :company_id
        ";

        $params = [
            'recurring_task_id' => $recurringTaskId,
            'task_type'         => TaskType::RECURRING_INSTANCE,
            'company_id'        => $this->tenantId(),
        ];

        $row = $this->fetchOne($sql, $params);

        return (int) ($row['instance_count'] ?? 0);
    }
}
