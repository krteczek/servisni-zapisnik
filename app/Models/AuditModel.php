<?php
declare(strict_types=1);

namespace App\Models;

final class AuditModel extends BaseModel
{
    protected string $table = 'audit_log';

    public function log(array $data): int
    {
        return $this->insert($data);
    }
}
