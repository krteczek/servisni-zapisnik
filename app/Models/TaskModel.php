<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;
use App\Core\Auth;

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
 
/**
 * Vrátí statistiky pro seznam úkolů
 */
public function getStatsForTasks(array $taskIds): array
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

    $sql = "
        SELECT 
            ta.task_id,
            COUNT(DISTINCT ta.id) AS reports_count,
            COALESCE(SUM(ta.kilometers), 0) AS total_km,
            COALESCE(SUM(ta.minutes_spent), 0) AS total_minutes,
            COUNT(DISTINCT tap.user_id) AS workers_count
        FROM task_assignments ta
        LEFT JOIN task_assignment_participants tap 
            ON tap.assignment_id = ta.id 
            AND tap.company_id = ta.company_id
        WHERE ta.task_id IN (" . implode(',', $placeholders) . ")
            AND ta.company_id = :company_id
        GROUP BY ta.task_id
    ";

    $stats = $this->fetchAll($sql, $params);

    $result = [];
    foreach ($stats as $stat) {
        $result[(int)$stat['task_id']] = [
            'reports_count' => (int)$stat['reports_count'],
            'total_km' => (int)$stat['total_km'],
            'total_minutes' => (int)$stat['total_minutes'],
            'workers_count' => (int)$stat['workers_count']
        ];
    }

    return $result;
}

/**
 * Upravený forIndex,
 */
 public function forIndex(?int $filterUserId = null): array
{
    $companyId = Auth::companyId();
    $userId    = Auth::id();
    $role      = Auth::role();

    $params = [
        'company_id' => $companyId,
        'status'     => 'open',
    ];
		$sql = "
		    SELECT 
		        t.*,
		
		        -- zakázka
		        w.title AS work_order_title,
		        w.status AS work_order_status,
		        w.priority AS work_order_priority,
		
		        -- tým
		        tm.name AS team_name,
		        tm.color AS team_color,
		
		        -- statistiky
		        COALESCE(r.reports_count, 0) AS reports_count,
		        COALESCE(r.total_minutes, 0) AS total_minutes,
		        COALESCE(r.total_km, 0) AS total_km
		
		    FROM tasks t
		
		    LEFT JOIN work_orders w 
		        ON w.id = t.work_order_id 
		        AND w.company_id = :company_id_work_order
		
		    LEFT JOIN admin.teams tm
		        ON tm.id = t.team_id
		        AND tm.company_id = :company_id_team
		
		    LEFT JOIN (
            SELECT
                ta.task_id,
                COUNT(*) AS reports_count,
                SUM(ta.kilometers) AS total_km,
                SUM(
                    ta.minutes_spent +
                    COALESCE((
                        SELECT SUM(tap.minutes_spent)
                        FROM task_assignment_participants tap
                        WHERE tap.assignment_id = ta.id
                    ), 0)
                ) AS total_minutes
            FROM task_assignments ta
            WHERE ta.company_id = :company_id_reports
            GROUP BY ta.task_id
        ) r ON r.task_id = t.id

        WHERE 
            t.company_id = :company_id
            AND t.status = :status
    ";

		$params['company_id_reports'] = $companyId;
		$params['company_id_work_order'] = $companyId;
		$params['company_id_team'] = $companyId;

    /*
     * ADMIN / MISTR
     */
    if (in_array($role, ['admin', 'mistr'], true)) {

        if ($filterUserId !== null) {
            $sql .= " AND t.created_by_user_id = :filter_user_id";
            $params['filter_user_id'] = $filterUserId;
        } else {
            $sql .= " AND t.created_by_user_id = :current_user_id";
            $params['current_user_id'] = $userId;
        }

        $sql .= " ORDER BY t.id DESC";
    }

    /*
     * PŘEDÁK / MONTÉR
     */
    else {

        $teamIds = Auth::teamIds(); // pole ID týmů

        if (empty($teamIds)) {
            return [];
        }

        $placeholders = [];
        foreach ($teamIds as $i => $teamId) {
            $key = "team_$i";
            $placeholders[] = ":$key";
            $params[$key] = $teamId;
        }

        $sql .= " AND t.team_id IN (" . implode(',', $placeholders) . ")";
        $sql .= " ORDER BY t.id DESC";
    }

    $tasks = $this->fetchAll($sql, $params);

    /*
     * Formátování hodin
     */
    foreach ($tasks as &$task) {
        $task['total_minutes'] = (int)$task['total_minutes'];
        $task['total_hours_formatted'] =
            floor($task['total_minutes'] / 60) . 'h ' .
            ($task['total_minutes'] % 60) . 'm';
    }

    return $tasks;
}
public function forIndexOld(): array
{
    $userId    = Auth::id();
    $role      = Auth::role();
    $companyId = Auth::companyId();

    // 🔹 základní dotaz pro work DB
    $sql = "
        SELECT *
        FROM {$this->tableName}
        WHERE company_id = :company_id
    ";

    $params = ['company_id' => $companyId];

    // 🔹 Předák / monter – jen úkoly ve svých týmech
    if (in_array($role, ['predak', 'monter'], true)) {
        $teamIds = (new TeamMembership())->activeTeamIdsForUser($userId);

        if (empty($teamIds)) {
            return []; // nic nevidí
        }

        $in = [];
        foreach ($teamIds as $i => $teamId) {
            $key = "team_$i";
            $in[] = ":$key";
            $params[$key] = $teamId;
        }

        $sql .= " AND team_id IN (" . implode(',', $in) . ")";
    }

    // 🔹 Admin / mistr – vidí vše, seřazeno podle toho, kdo úkol vytvořil
    if (in_array($role, ['admin', 'mistr'], true)) {
        $sql .= "
            ORDER BY 
                (created_by_user_id = :user_id) DESC,
                id DESC
        ";
        $params['user_id'] = $userId;
    } else {
        $sql .= " ORDER BY id DESC";
    }

    // 🔹 Načteme úkoly
    $tasks = $this->fetchAll($sql, $params);

    if (empty($tasks)) {
        return [];
    }

    // 🔹 Separátně načíst týmy z admin DB
    $teamIds = array_unique(array_column($tasks, 'team_id'));
    $teams = (new TeamModel())->getColorsAndNamesByIds($teamIds); 

    // 🔹 Separátně načíst zakázky z work DB
    $workOrderIds = array_unique(array_filter(array_column($tasks, 'work_order_id')));
    $workOrders = (new WorkOrderModel())->getNamesByIds($workOrderIds); 

    // 🔹 Pro každý úkol načteme statistiky z task_assignments
    foreach ($tasks as &$task) {
        $tid = $task['team_id'];
        $wid = $task['work_order_id'];

        $task['team_name'] = $teams[$tid]['name'] ?? '';
        $task['team_color'] = $teams[$tid]['color'] ?? '#999';
        $task['work_order_name'] = $workOrders[$wid]['name'] ?? '';
        $task['work_order_priority'] = $workOrders[$wid]['priority'] ?? '';
        // 🔹 Statistiky z task_assignments
        $stats = $this->fetchOne(
            "SELECT 
                COUNT(*) AS reports_count,
                COALESCE(SUM(kilometers), 0) AS total_km,
                COALESCE(SUM(minutes_spent), 0) AS total_minutes
             FROM task_assignments 
             WHERE task_id = :task_id 
               AND company_id = :company_id",
            [
                'task_id' => $task['id'],
                'company_id' => $companyId
            ]
        );

        $task['reports_count'] = (int) ($stats['reports_count'] ?? 0);
        $task['total_km'] = (int) ($stats['total_km'] ?? 0);
        $task['total_minutes'] = (int) ($stats['total_minutes'] ?? 0);
        $task['total_hours'] = round($task['total_minutes'] / 60, 1);
        $task['total_hours_formatted'] = floor($task['total_minutes'] / 60) . 'h ' . ($task['total_minutes'] % 60) . 'm';
    }

    return $tasks;
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
    
}
