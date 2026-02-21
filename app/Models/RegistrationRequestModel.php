<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class RegistrationRequestModel extends BaseModel
{
    protected string $table = 'registration_requests';

    // důležité: tohle je globální tabulka, ne tenant
    protected bool $tenantAware = false;
    protected string $connection = 'admin';

    public function upsert(array $data): void
    {
        $sql = "
            INSERT INTO {$this->tableName}
                (email, token_hash, expires_at, ip_address, user_agent)
            VALUES
                (:email, :token_hash, :expires_at, :ip_address, :user_agent)
            ON DUPLICATE KEY UPDATE
                token_hash = VALUES(token_hash),
                expires_at = VALUES(expires_at),
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent)
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($data);
    }

    public function findValidByHash(string $hash): ?array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE token_hash = :hash
              AND expires_at > NOW()
            LIMIT 1
        ";

        return $this->fetchOne($sql, ['hash' => $hash]);
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->db()->prepare(
            "DELETE FROM {$this->tableName} WHERE id = :id"
        );

        $stmt->execute(['id' => $id]);
    }
}