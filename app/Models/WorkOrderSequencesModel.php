<?php
declare(strict_types=1);

namespace App\Models;
use App\Core\Database;
use PDO;
use App\Core\Auth;

class WorkOrderSequencesModel extends BaseModel
{
    protected string $table = 'work_order_sequences';
    protected string $connection = 'work';

 public function next(int $year, int $companyId = 0): int
{
    $db = $this->db(); // ✅ KLÍČOVÉ

    if ($companyId === 0) {
        $companyId = $this->tenantId();
    }

    $stmt = $db->prepare("
        SELECT last_number
        FROM {$this->tableName}
        WHERE company_id = :cid AND year = :year
        FOR UPDATE
    ");
    $stmt->execute([
        'cid'  => $companyId,
        'year' => $year,
    ]);

    $row = $stmt->fetch();

    if (!$row) {
        $db->prepare("
            INSERT INTO {$this->tableName} (company_id, year, last_number)
            VALUES (:cid, :year, 1)
        ")->execute([
            'cid'  => $companyId,
            'year' => $year,
        ]);

        return 1;
    }

    $next = (int)$row['last_number'] + 1;

    $db->prepare("
        UPDATE {$this->tableName}
        SET last_number = :num
        WHERE company_id = :cid AND year = :year
    ")->execute([
        'num'  => $next,
        'cid'  => $companyId,
        'year' => $year,
    ]);

    return $next;
}

}