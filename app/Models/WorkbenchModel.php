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
            'myTeamTasks'     => $this->openTasksForMyTeams($teamIds),
            //'otherTeamTasks'  => $this->openTasksForOtherTeams(),
            //'recurringTasks'  => $this->activeRecurringTasks(),
        ];
    }

    private function myTeams(): array
    {
        $sql = "SELECT
                t.id,
                t.name,
                t.color,
                tm.role_in_team

            FROM teams t

            INNER JOIN team_memberships tm
                ON tm.team_id = t.id

            WHERE
                t.company_id = :company_id_t
                AND tm.company_id = :company_id_tm
                AND tm.user_id = :user_id
                AND t.active = 1
                AND (
                    tm.valid_to IS NULL
                    OR tm.valid_to >= CURDATE()
                )

            ORDER BY t.name ASC
        ";

        return $this->fetchAll($sql, [
            ':company_id_t'  => Auth::companyId(),
            ':company_id_tm' => Auth::companyId(),
            ':user_id'       => Auth::id(),
        ]);

    }

private function openTasksForMyTeams(array $teamIds): array
{
    if ($teamIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

    $sql = 
        "SELECT t.id,
            t.title,
            t.status,
            t.due_date,
            t.team_id,
            t.work_order_id,
            t.created_at,

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

}
