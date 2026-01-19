<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AuditLogModel;
use App\Models\AuditModel;
use Throwable;

final class AuditLogger
{
    public static function log(
        string $action,
        string $table,
        ?int $recordId = null,
        ?array $before = null,
        ?array $after = null
    ): void {
        try {
(new AuditModel())->log([
    'user_id'    => Auth::id(),
    'action'     => $action,
    'entity'     => $table,
    'entity_id'  => $recordId,
    'old_values' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
    'new_values' => $after  ? json_encode($after,  JSON_UNESCAPED_UNICODE) : null,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
]);
        } catch (\Throwable $e) {
    file_put_contents(
        __DIR__ . '/../../storage/audit_errors.log',
        date('Y-m-d H:i:s') . ' ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );
}
    }
}
