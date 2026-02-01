<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class AuthTokenModel extends BaseModel
{
    protected string $table = 'auth_tokens';
    protected string $connection = 'admin';

    /* ==========================================================
     * TRANSACTIONS
     * ========================================================== */

    public function begin(): void
    {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    public function rollback(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    /* ==========================================================
     * FIND VALID TOKEN
     * ========================================================== */

    public function findValidByHash(string $hash, string $type): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM {$this->tableName}
             WHERE token_hash = :hash
               AND type = :type
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1"
        );

        $stmt->execute([
            'hash' => $hash,
            'type' => $type,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
     * INVALIDATE OLD TOKENS
     * ========================================================== */

    public function invalidateForUser(
        int $userId,
        int $companyId,
        string $type
    ): int {
        $stmt = $this->db->prepare(
            "UPDATE {$this->tableName}
             SET used_at = NOW()
             WHERE user_id = :user_id
               AND company_id = :company_id
               AND type = :type
               AND used_at IS NULL"
        );

        $stmt->execute([
            'user_id'    => $userId,
            'company_id' => $companyId,
            'type'       => $type,
        ]);

        return $stmt->rowCount();
    }

    /* ==========================================================
     * MARK AS USED (AUDIT SE CHYTÍ AUTOMATICKY)
     * ========================================================== */

    public function markUsed(int $id): bool
    {
        return $this->updateRow($id, [
            'used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /* ==========================================================
     * HOUSEKEEPING
     * ========================================================== */

    public function deleteExpired(): int
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->tableName}
             WHERE expires_at < NOW()
               AND used_at IS NOT NULL"
        );

        $stmt->execute();

        return $stmt->rowCount();
    }
}
