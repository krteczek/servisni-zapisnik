<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Auth;

class WorkbenchModel extends BaseModel
{
    protected string $table = 'tasks';
    protected string $connection = 'work';
    protected bool $tenantAware = true;
    
    public function forIndex(): array
    {
        $myTeams = $this->myTeams();
        $teamIds = array_column($myTeams, 'id');
        
        return [
            'myTeams'         => $myTeams,
            'otherTeams'      => $this->otherTeams($teamIds),
            'myTeamTasks'     => $this->openTasksForMyTeams($teamIds),
            'otherTeamTasks'  => $this->openTasksForOtherTeams($teamIds),
            //'myBillingTasks'    => $this->myBillingTasks($teamIds),
            //'otherBillingTasks'    => $this->otherBillingTasks($teamIds),
            //'myPrepareToDoneTask' => $this->myPrepareToDoneTask($teamIds),
            //'otherPrepareToDoneTask' => $this->otherPrepareToDoneTask($teamIds),
        ];
    }

private function myTeams(): array
{
    $sql = "SELECT DISTINCT
            t.id,
            t.name,
            t.color,
            tm.role_in_team

        FROM team_memberships tm

        INNER JOIN teams t
            ON t.id = tm.team_id
           AND t.company_id = tm.company_id

        WHERE
            tm.company_id = :company_id
            AND tm.user_id = :user_id
            AND t.active = 1

            AND tm.valid_from <= CURDATE()

            AND (
                tm.valid_to IS NULL
                OR tm.valid_to > CURDATE()
            )

        ORDER BY t.name ASC
    ";

    return $this->fetchAll($sql, [
        ':company_id' => Auth::companyId(),
        ':user_id'    => Auth::id(),
    ]);
}

private function otherTeams(array $teamIds): array
{
    $sql = 
        "SELECT
            t.id,
            t.name,
            t.color

        FROM teams t

        WHERE
            t.company_id = ?
            AND t.active = 1
            
    ";

    $params = [Auth::companyId()];

    if ($teamIds !== []) {
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

        $sql .= " AND t.id NOT IN ($placeholders)";

        $params = array_merge($params, $teamIds);
    }

    $sql .= " ORDER BY t.name ASC";

    return $this->fetchAll($sql, $params);
}


private function openTasksForMyTeams(array $teamIds): array
{
    if ($teamIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

    $sql =
        "SELECT
            t.id,
            t.title,
            t.status,
            t.due_date,
            t.team_id,
            t.work_order_id,
            t.created_at,
            t.is_recurring,
            t.recurring_task_id,

            EXISTS (
                SELECT 1
                FROM recurring_tasks rt
                WHERE rt.task_id = t.id
            ) AS is_recurring_master,

            wo.title AS work_order_title,
            wo.priority AS work_order_priority,

            tm.name AS team_name,
            tm.color AS team_color

        FROM tasks t

        INNER JOIN work_orders wo
            ON wo.id = t.work_order_id
           AND wo.company_id = t.company_id

        INNER JOIN teams tm
            ON tm.id = t.team_id
           AND tm.company_id = t.company_id

        WHERE
            t.company_id = ?
            AND t.team_id IN ($placeholders)
            AND t.status = 'open'

        ORDER BY
            t.due_date IS NULL,
            t.due_date ASC,

            FIELD(
                wo.priority,
                'emergency',
                'high',
                'normal',
                'low'
            ),

            t.created_at ASC

        LIMIT 25
    ";

    return $this->fetchAll(
        $sql,
        array_merge([Auth::companyId()], $teamIds)
    );
}


private function openTasksForOtherTeams(array $teamIds): array
{
    $sql = "SELECT
            t.id,
            t.title,
            t.status,
            t.due_date,
            t.team_id,
            t.work_order_id,
            t.created_at,
            t.is_recurring,
            t.recurring_task_id,

            EXISTS (
                SELECT 1
                FROM recurring_tasks rt
                WHERE rt.task_id = t.id
            ) AS is_recurring_master,

            wo.title AS work_order_title,
            wo.priority AS work_order_priority,

            tm.name AS team_name,
            tm.color AS team_color

        FROM tasks t

        INNER JOIN work_orders wo
            ON wo.id = t.work_order_id
           AND wo.company_id = t.company_id

        INNER JOIN teams tm
            ON tm.id = t.team_id
           AND tm.company_id = t.company_id

        WHERE
            t.company_id = ?
            AND t.status = 'open'
    ";

    $params = [Auth::companyId()];

    if ($teamIds !== []) {
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

        $sql .= " AND t.team_id NOT IN ($placeholders)";

        $params = array_merge($params, $teamIds);
    }

    $sql .= "
        ORDER BY
            t.due_date IS NULL,
            t.due_date ASC,

            FIELD(
                wo.priority,
                'emergency',
                'high',
                'normal',
                'low'
            ),

            t.created_at ASC

        LIMIT 25
    ";

    return $this->fetchAll($sql, $params);
}

}