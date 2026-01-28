<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

final class AuditLogCore
{
    public static function log(
        string $entity,
        int|string|null $entityId,
        string $action,
        array $diff = []
    ): void {
        if (!self::isEnabled()) {
            return;
        }

        try {
            self::logInsert([
                'company_id'  => Auth::companyId(),
                'user_id'     => Auth::id(),
                'user_email'  => Auth::email(),
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'diff'        => $diff ? json_encode($diff, JSON_THROW_ON_ERROR) : null,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Throwable $e) {
            // audit je BEST-EFFORT – NESMÍ nikdy shodit aplikaci
            // ticho je zde záměrné
        }
    }

    private static function logInsert(array $row): void
    {
        $db = Database::admin();

        $sql = '
            INSERT INTO audit_logs
            (company_id, user_id, user_email, action, entity, entity_id, diff, ip_address, user_agent)
            VALUES
            (:company_id, :user_id, :user_email, :action, :entity, :entity_id, :diff, :ip_address, :user_agent)
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute($row);
    }

    private static function isEnabled(): bool
    {
        return Config::get('audit.enabled', true);
    }
}
