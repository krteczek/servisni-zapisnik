<?php
declare(strict_types=1);
namespace App\Models;

final class RecurringRunnerModel extends BaseModel
{
    protected string $table = 'recurring_tasks';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;

public function findDueTasks(int $companyId, int $limit): array
{
    return $this->fetchAll(
        "SELECT *
        FROM {$this->tableName}
        WHERE active = 1
          AND next_due_date <= CURDATE()
          AND company_id = :company_id
          AND (
               processing_at IS NULL
               OR processing_at < NOW() - INTERVAL 5 MINUTE
          )
        ORDER BY next_due_date ASC
        LIMIT {$limit}
    ", [
        'company_id' => $companyId
    ]);
}


    public function taskAlreadyExistsForDate(
        int $rtId,
        int $companyId,
        string $dueDate
    ): bool {
        $sql = 
            "SELECT id
            FROM tasks
            WHERE recurring_task_id = :rt_id
            AND due_date = :due_date
            AND company_id = :company_id
            LIMIT 1
        ";
        return (bool) $this->fetchOne($sql, [
            'rt_id'      => $rtId,
            'due_date'   => $dueDate,
            'company_id' => $companyId,
        ]);
    }


    public function updateNextDueDate(int $id, int $companyId, string $next): void
    {
        $this->update($id, [
            'next_due_date' => $next
        ]);
    }

public function lockTask(int $id, int $companyId): bool
{
    $stmt = $this->db()->prepare("
        UPDATE {$this->tableName}
        SET processing_at = NOW()
        WHERE id = :id
          AND company_id = :company_id
          AND (
              processing_at IS NULL
              OR processing_at < NOW() - INTERVAL 5 MINUTE
          )
    ");

    $stmt->execute([
        'id' => $id,
        'company_id' => $companyId
    ]);

    return $stmt->rowCount() > 0;
}

public function clearProcessing(int $id, int $companyId): void
{
    $stmt = $this->db()->prepare("
        UPDATE {$this->tableName}
        SET processing_at = NULL
        WHERE id = :id
          AND company_id = :company_id
    ");

    $stmt->execute([
        'id' => $id,
        'company_id' => $companyId
    ]);
}


}