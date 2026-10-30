<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\Guards\BanType;

class BanModel extends BaseModel
{
    protected string $table = 'bans';
    protected bool $tenantAware = false;

    public function isBanned(int $fingerprint): bool
    {
        $sql = "
            SELECT id, type, fingerprint, banned_until, NOW() AS db_now
            FROM {$this->tableName}
            WHERE fingerprint = ?
            AND banned_until > NOW()
            AND type = ?
            LIMIT 1
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$fingerprint, BanType::SCANNING_BAN]);

        $result = $stmt->fetchColumn();

        

        return (bool) $result;
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
	public function createBan(array $data): void
	{
	    $this->create($data);
	}

    public function isAccountBanned(int $userId): bool
    {
        $sql = "
            SELECT id
            FROM {$this->tableName}
            WHERE user_id = ?
            AND type = ?
            AND banned_until > NOW()
            LIMIT 1
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$userId, BanType::ACCOUNT_BAN]);
        $result = $stmt->fetchColumn();

        return (bool) $result;
    }
}
