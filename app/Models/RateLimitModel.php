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
        string $ua
    ): void {
        $sql = "
            INSERT INTO {$this->table}
            (action, tenant_slug, email, ip, ua)
            VALUES (:action, :tenant, :email, :ip, : ua)
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'action' => $action,
            'tenant' => $tenant,
            'email'  => $email,
            'ip'     => $ip,
            'ua'     => $ua,
        ]);
    }

    public function countRecentAttempts(
        string $action,
        string $tenant,
        string $email,
        string $ip,
        string $ua,
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
              AND ua = :ua
              AND created_at >= (NOW() - INTERVAL $minutes MINUTE)
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'action' => $action,
            'tenant' => $tenant,
            'email'  => $email,
            'ip'     => $ip,
            'ua'     => $ua,
            'window' => $windowMinutes,
        ]);

        return (int) $stmt->fetchColumn();
    }
}