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
        $lastId = $this->db->lastInsertId();

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

        return (int) $lastId;
    }

    /* ==========================================================
     * UPDATE (before + after, až po úspěchu)
     * ========================================================== */

protected function updateRow(int $id, array $data): bool
{
    $before = $this->findRow($id);

    if (!$before) {
        return false; // záznam neexistuje
    }

    $set = [];
    foreach ($data as $key => $val) {
        $set[] = "{$key} = :{$key}";
    }

    $sql = "UPDATE {$this->tableName}
            SET " . implode(', ', $set) . "
            WHERE id = :id";

    $data['id'] = $id;

    $stmt = $this->db->prepare($sql);
    $ok = $stmt->execute($data);

    if ($ok) {
        try {
            AuditLogCore::logUpdate(
                table: $this->table,
                recordId: $id,
                before: $before,
                after: $data
            );
        } catch (Throwable) {}
    }

    return $ok;
}
    protected function updateRowOld(int $id, array $data): void
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
   
   public function updateWhere(array $where, array $data): bool
{
    if ($where === [] || $data === []) {
        throw new \InvalidArgumentException('updateWhere: prázdná data nebo podmínky');
    }

    $setParts   = [];
    $whereParts = [];
    $params     = [];

    foreach ($data as $column => $value) {
        $setParts[] = "{$column} = :set_{$column}";
        $params["set_{$column}"] = $value;
    }

    foreach ($where as $column => $value) {
        $whereParts[] = "{$column} = :where_{$column}";
        $params["where_{$column}"] = $value;
    }

    $sql = sprintf(
        "UPDATE %s SET %s WHERE %s",
        $this->tableName,
        implode(', ', $setParts),
        implode(' AND ', $whereParts)
    );

    $stmt = $this->db->prepare($sql);

    return $stmt->execute($params);
}

}
