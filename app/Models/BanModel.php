<?php
declare(strict_types=1);

namespace App\Models;


class BanModel extends BaseModel
{
    protected string $table = 'bans';
    protected bool $tenantAware = false;

    public function isBanned(int $fingerprint): bool
    {
        $sql = "
            SELECT id
            FROM {$this->tableName}
            WHERE fingerprint = ?
            AND banned_until > NOW()
            LIMIT 1
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$fingerprint]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
	public function createBan(array $data): void
	{
	    $this->create($data);
	}

}
