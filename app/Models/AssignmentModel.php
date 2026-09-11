<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Transaction;
use App\Models\UserModel;

final class AssignmentModel extends BaseModel
{
    protected string $table = 'task_assignments';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;

    /**
     * Najde všechny reporty pro task (s účastníky)
     */
    public function findByTask(int $taskId): array
    {
        $sql = "
            SELECT 
                ta.*,
                u.first_name as created_by_first_name,
                u.last_name as created_by_last_name
            FROM {$this->tableName} ta
            LEFT JOIN users u ON u.id = ta.created_by_user_id
            WHERE ta.task_id = :task_id 
                AND ta.company_id = :company_id
            ORDER BY ta.created_at DESC
        ";
        
        $reports = $this->fetchAll($sql, [
            'task_id' => $taskId,
            'company_id' => $this->tenantId()
        ]);
        
        // Načteme účastníky pro každý report
        foreach ($reports as &$report) {
            $report['participants'] = $this->getParticipants($report['id']);
        }
        unset($report);
        return $reports;
    }

    /**
     * Načte účastníky pro konkrétní report
     */
    private function getParticipants(int $assignmentId): array 
    {
        $sql = "
            SELECT *
            FROM task_assignment_participants
            WHERE assignment_id = :assignment_id
            AND company_id = :company_id

            ";
            return $this->fetchAll($sql, [
                'assignment_id' => $assignmentId,
                'company_id' => $this->tenantId()
            ]);
    }
   /**
     * Načte účastníky všech reportů daného úkolu a spočítá jim čas strávený na úkole
     */

    public function getTaskParticipants(int $taskId): array
    {
        $sql = "
            SELECT
                tap.user_id,
                tap.first_name,
                tap.last_name,
                SUM(tap.minutes_spent) AS minutes_spent
            FROM task_assignment_participants tap
            JOIN task_assignments ta
                ON ta.id = tap.assignment_id
            AND ta.company_id = tap.company_id
            WHERE ta.task_id = :task_id
            AND ta.company_id = :company_id
            GROUP BY
                tap.user_id,
                tap.first_name,
                tap.last_name
            ORDER BY
                tap.last_name,
                tap.first_name
        ";

        return $this->fetchAll($sql, [
            'task_id' => $taskId,
            'company_id' => $this->tenantId()
        ]);
    }


    /**
     * Vytvoří nový report i s účastníky
     */
    public function createReport(int $taskId, int $workOrderId, array $data): int
    {
        //dd($data);
        return Transaction::run(function () use ($taskId, $workOrderId, $data) {

            $totalMinutes = 0;
            $participants = [];

            foreach ($data['participants'] as $userId => $pData) {
                if (!empty($pData['selected'])) {
                    $minutes = (int) $pData['minutes_spent'];
                    if ($minutes !== 0) {
                        $totalMinutes += $minutes;
                        $participants[$userId] = $minutes;
                    }
                }
            }

            $assignmentId = $this->create([
                'task_id' => $taskId,
                'work_order_id' => $workOrderId,
                'user_id' => Auth::id(),
                'minutes_spent' => $totalMinutes,
                'kilometers' => (int) ($data['kilometers'] ?? 0),
                'note' => $data['report'] ?? '',
                'created_by_user_id' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$assignmentId) {
                throw new \RuntimeException('Assignment create failed');
            }

            foreach ($participants as $userId => $minutes) {
                if (!$this->addParticipant($assignmentId, $userId, $minutes)) {
                    throw new \RuntimeException('Participant insert failed');
                }
            }

            return $assignmentId;
        });
    }


    /**
     * Přidá účastníka k reportu
     */
private function addParticipant(int $assignmentId, int $userId, int $minutes): bool
{
    $user = (new UserModel())->find($userId);

    if (!$user) {
        return false;
    }

    $sql = "
        INSERT INTO task_assignment_participants
            (
                assignment_id,
                user_id,
                first_name,
                last_name,
                minutes_spent,
                company_id,
                created_at
            )
        VALUES
            (
                :assignment_id,
                :user_id,
                :first_name,
                :last_name,
                :minutes,
                :company_id,
                :created_at
            )
    ";

    $stmt = $this->db()->prepare($sql);

    return $stmt->execute([
        'assignment_id' => $assignmentId,
        'user_id' => $userId,
        'first_name' => $user['first_name'] ?? '',
        'last_name' => $user['last_name'] ?? '',
        'minutes' => $minutes,
        'company_id' => $this->tenantId(),
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

    /**
     * Aktualizuje celkový čas reportu podle času jeho účastníků.
     */
    public function updateTotalMinutes(int $assignmentId): bool
    {
        $sql = "
            UPDATE {$this->tableName} ta
            SET ta.minutes_spent = (
                SELECT COALESCE(SUM(minutes_spent), 0)
                FROM task_assignment_participants
                WHERE assignment_id = :assignment_id
                  AND company_id = :company_id
            )
            WHERE ta.id = :assignment_id
              AND ta.company_id = :company_id
        ";
        
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([
            'assignment_id' => $assignmentId,
            'company_id' => $this->tenantId()
        ]);
    }


public function getTaskTotalKilometers(int $taskId): int
{
    $sql = "
        SELECT COALESCE(SUM(kilometers), 0) AS total_kilometers
        FROM task_assignments
        WHERE task_id = :task_id
          AND company_id = :company_id
    ";

    $result = $this->fetchAll($sql, [
        'task_id' => $taskId,
        'company_id' => $this->tenantId()
    ]);

    return (int) ($result[0]['total_kilometers'] ?? 0);
}
}