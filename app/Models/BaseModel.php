<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\AuditLogCore;
use PDO;
use LogicException;
use Throwable;

abstract class BaseModel
{
    protected PDO $db;

    /** název tabulky BEZ prefixu (logická entita) */
    protected string $table;

    /** finální název tabulky S prefixem (DB implementace) */
    protected string $tableName;

    public function __construct()
    {
        $this->db = Database::pdo();

        if (!isset($this->table) || $this->table === '') {
            throw new LogicException(
                static::class . ' must define protected string $table'
            );
        }

        $this->tableName = Database::table($this->table);
    }

    /* ==========================================================
     * ZÁKLADNÍ SELECTY
     * ========================================================== */

    protected function allRows(string $orderBy = 'id'): array
    {
        return $this->db
            ->query("SELECT * FROM {$this->tableName} ORDER BY {$orderBy}")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function findRow(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
     * INSERT (jen nové data)
     * ========================================================== */

    protected function insert(array $data): int
    {
        $cols   = array_keys($data);
        $fields = implode(', ', $cols);
        $values = ':' . implode(', :', $cols);

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->tableName} ({$fields}) VALUES ({$values})"
        );
        $stmt->execute($data);

        // audit – insert = pouze nové hodnoty
        try {
            AuditLogCore::logInsert(
                table: $this->table,
                recordId: null,
                after: $data
            );
        } catch (Throwable) {
            // audit NIKDY nesmí rozbít aplikaci
        }

        return (int) $this->db->lastInsertId();
    }

    /* ==========================================================
     * UPDATE (before + after, až po úspěchu)
     * ========================================================== */

    protected function updateRow(int $id, array $data): void
    {
        // 1️⃣ stáhneme původní stav
        $before = $this->findRow($id);

        if (!$before) {
            return;
        }

        // 2️⃣ provedeme update
        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$this->tableName}
                SET " . implode(', ', $set) . "
                WHERE id = :id";

        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        // 3️⃣ audit až PO úspěšném update
        try {
            AuditLogCore::logUpdate(
                table: $this->table,
                recordId: $id,
                before: $before,
                after: $data
            );
        } catch (Throwable) {
            // audit je best-effort
        }
    }
}
