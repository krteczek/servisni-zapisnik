<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use LogicException;

abstract class TenantModel extends BaseModel
{
    protected function tenantId(): int
    {
        $companyId = Auth::companyId();

        if (!$companyId) {
            throw new LogicException('Tenant (company) context missing');
        }

        return $companyId;
    }

    protected function tenantColumn(): string
    {
        return 'company_id';
    }

    final public function all(): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE {$this->tenantColumn()} = :tenant
            ORDER BY id DESC
        ";

        return $this->fetchAll($sql, [
            'tenant' => $this->tenantId(),
        ]);
    }

    final public function find(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE id = :id
              AND {$this->tenantColumn()} = :tenant
            LIMIT 1
        ";

        return $this->fetchOne($sql, [
            'id'     => $id,
            'tenant' => $this->tenantId(),
        ]);
    }
    final public function update(int $id, array $data): bool
{
    if ($data === []) {
        return false;
    }

    $set = [];
    foreach ($data as $key => $val) {
        $set[] = "{$key} = :{$key}";
    }

    $data['id']     = $id;
    $data['tenant'] = $this->tenantId();

    $sql = "
        UPDATE {$this->tableName}
        SET " . implode(', ', $set) . "
        WHERE id = :id
          AND {$this->tenantColumn()} = :tenant
    ";

    $stmt = $this->db()->prepare($sql);
    return $stmt->execute($data);
}

final public function create(array $data): int
{
    if ($data === []) {
        throw new LogicException('Create: empty data');
    }

    // vnutí tenant kontext
    $data[$this->tenantColumn()] = $this->tenantId();

    return $this->insert($data);
}

}
