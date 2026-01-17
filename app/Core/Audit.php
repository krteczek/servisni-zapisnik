<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AuditModel;

final class Audit
{
    public static function log(
        string $entity,
        int $entityId,
        string $action,
        array $changes = []
    ): void {
        $userId = Auth::id(); // nebo null

        $data = [
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'action'     => $action,
            'changes'    => $changes ? json_encode($changes, JSON_THROW_ON_ERROR) : null,
            'user_id'    => $userId,
            'created_at'=> date('Y-m-d H:i:s'),
        ];

        (new AuditModel())->log($data);
    }
}
