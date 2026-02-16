<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;

final class AssignmentModel extends BaseModel
{
    protected string $table = 'task_assignments';
    protected string $connection = 'work';
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
            LEFT JOIN admin.users u ON u.id = ta.created_by_user_id
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
        
        return $reports;
    }

    /**
     * Načte účastníky pro konkrétní report
     */
    private function getParticipants(int $assignmentId): array
    {
        $sql = "
            SELECT 
                tap.user_id,
                tap.minutes_spent,
                u.first_name,
                u.last_name
            FROM task_assignment_participants tap
            LEFT JOIN admin.users u ON u.id = tap.user_id
            WHERE tap.assignment_id = :assignment_id
                AND tap.company_id = :company_id
            ORDER BY u.last_name, u.first_name
        ";
        
        return $this->fetchAll($sql, [
            'assignment_id' => $assignmentId,
            'company_id' => $this->tenantId()
        ]);
    }

    /**
     * Vytvoří nový report i s účastníky
     */
     
public function createReport(int $taskId, int $workOrderId, array $data): int
{
    // Spočítáme celkový čas z účastníků
    $totalMinutes = 0;
    $participants = [];
    
    foreach ($data['participants'] as $userId => $pData) {
        if (!empty($pData['selected'])) {
            $minutes = ((int)($pData['hours'] ?? 0) * 60) + (int)($pData['minutes'] ?? 0);
            if ($minutes > 0) {
                $totalMinutes += $minutes;
                $participants[$userId] = $minutes;
            }
        }
    }
    
    // 1. Vytvoříme report s celkovým časem
    $assignmentId = $this->create([
        'task_id' => $taskId,
        'work_order_id' => $workOrderId,
        'user_id' => Auth::id(),
        'minutes_spent' => $totalMinutes,  // ✅ celkový čas rovnou
        'kilometers' => (int) ($data['kilometers'] ?? 0),
        'note' => $data['report'] ?? '',
        'created_by_user_id' => Auth::id(),
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // 2. Přidáme účastníky
    foreach ($participants as $userId => $minutes) {
        $this->addParticipant($assignmentId, $userId, $minutes);
    }
    
    return $assignmentId;
}


    public function createReportOld(int $taskId, int $workOrderId, array $data): int
    {
        // 1. Vytvoříme samotný report
        $assignmentId = $this->create([
            'task_id' => $taskId,
            'work_order_id' => $workOrderId,
            'user_id' => Auth::id(),  // kdo report vytvořil
            'minutes_spent' => 0,      // celkový čas se bude počítat z účastníků
            'kilometers' => (int) ($data['kilometers'] ?? 0),
            'note' => $data['report'] ?? '',
            'created_by_user_id' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if (!$assignmentId) {
            return 0;
        }
        
        // 2. Přidáme účastníky
        if (!empty($data['participants'])) {
            foreach ($data['participants'] as $userId => $participantData) {
                if (!empty($participantData['selected'])) {
                    $hours = (int) ($participantData['hours'] ?? 0);
                    $minutes = (int) ($participantData['minutes'] ?? 0);
                    $totalMinutes = ($hours * 60) + $minutes;
                    
                    if ($totalMinutes > 0) {
                        $this->addParticipant($assignmentId, $userId, $totalMinutes);
                    }
                }
            }
        }
        
        return $assignmentId;
    }

    /**
     * Přidá účastníka k reportu
     */
    private function addParticipant(int $assignmentId, int $userId, int $minutes): bool
    {
        $sql = "
            INSERT INTO task_assignment_participants 
                (assignment_id, user_id, minutes_spent, company_id, created_at)
            VALUES 
                (:assignment_id, :user_id, :minutes, :company_id, :created_at)
        ";
        
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([
            'assignment_id' => $assignmentId,
            'user_id' => $userId,
            'minutes' => $minutes,
            'company_id' => $this->tenantId(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Aktualizuje celkový čas v reportu (můžeš použít triggr nebo dopočítat)
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

    /**
     * Vrátí statistiky pro úkoly
     */
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
        
        $sql = "
            SELECT 
                ta.task_id,
                COUNT(DISTINCT ta.id) as total_assignments,
                COALESCE(SUM(tap.minutes_spent), 0) as total_minutes,
                COALESCE(SUM(ta.kilometers), 0) as total_kilometers,
                COUNT(DISTINCT tap.user_id) as total_workers
            FROM {$this->tableName} ta
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
                'total_assignments' => (int)$stat['total_assignments'],
                'total_minutes' => (int)$stat['total_minutes'],
                'total_kilometers' => (int)$stat['total_kilometers'],
                'total_workers' => (int)$stat['total_workers']
            ];
        }
        
        return $result;
    }
}