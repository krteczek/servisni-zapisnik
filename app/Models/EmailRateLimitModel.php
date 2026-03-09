<?php
declare(strict_types=1);

namespace App\Models;



final class EmailRateLimitModel extends BaseModel
{
    protected string $table = 'email_rate_limits';

    public function logAttempt(
        string $action,
        string $tenant,
        string $email,
        string $ip
    ): void {
        $sql = "
            INSERT INTO {$this->table}
            (action, tenant_slug, email, ip)
            VALUES (:action, :tenant, :email, :ip)
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'action' => $action,
            'tenant' => $tenant,
            'email'  => $email,
            'ip'     => $ip,
        ]);
    }

    public function countRecentAttempts(
        string $action,
        string $tenant,
        string $email,
        string $ip,
        int $windowMinutes
    ): int {
    	  $minutes = (int)$windowMinutes;
        $sql = "
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE action = :action
              AND tenant_slug = :tenant
              AND email = :email
              AND ip = :ip
              AND created_at >= (NOW() - INTERVAL $minutes MINUTE)
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'action' => $action,
            'tenant' => $tenant,
            'email'  => $email,
            'ip'     => $ip,
            'window' => $windowMinutes,
        ]);

        return (int) $stmt->fetchColumn();
    }
}