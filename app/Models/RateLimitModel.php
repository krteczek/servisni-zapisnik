<?php
declare(strict_types=1);

namespace App\Models;



final class RateLimitModel extends BaseModel
{
    protected string $table = 'email_rate_limits';

    public function logAttempt(
        string $action,
        string $tenant,
        string $email,
        string $ip,
        string $ua,
        int $fingerprint
    ): void {
        $sql = "
            INSERT INTO {$this->table}
            (action, tenant_slug, email, ip, ua, fingerprint)
            VALUES (:action, :tenant, :email, :ip, :ua, :fingerprint)
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'action'      => $action,
            'tenant'      => $tenant,
            'email'       => $email,
            'ip'          => $ip,
            'ua'          => $ua,
            'fingerprint' => $fingerprint,
        ]);
    }

    public function countRecentAttempts(
        string $action,
        int $fingerprint,
        int $windowMinutes
    ): int {
    	  $minutes = $windowMinutes;
        $sql = "
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE action = :action
              AND fingerprint = :fingerprint
              AND created_at >= (NOW() - INTERVAL $minutes MINUTE)
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'action' => $action,
            'fingerprint' => $fingerprint
        ]);

        return (int) $stmt->fetchColumn();
    }
}