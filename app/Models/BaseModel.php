<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
protected PDO $db;

    /** název tabulky BEZ prefixu */
    protected string $table;

    /** finální název tabulky S prefixem */
    protected string $tableName;

    public function __construct()
    {
        $this->db = Database::pdo();

        if (!isset($this->table) || $this->table === '') {
            throw new \LogicException(
                static::class . ' must define protected string $table'
            );
        }

        $this->tableName = Database::table($this->table);
    }

protected function allRows(string $orderBy = 'id'): array
{
    return $this->db
        ->query("SELECT * FROM {$this->tableName} ORDER BY {$orderBy}")
        ->fetchAll();
}
protected function findRow(int $id): ?array
{
    $stmt = $this->db->prepare(
        "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1"
    );
    $stmt->execute(['id' => $id]);

    return $stmt->fetch() ?: null;
}
    protected function insert(array $data): int
    {
        $cols = array_keys($data);
        $fields = implode(', ', $cols);
        $values = ':' . implode(', :', $cols);

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->tableName} ({$fields}) VALUES ({$values})"
        );
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    protected function updateRow(int $id, array $data): void
    {
        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $set) . " WHERE id = :id";
        $data['id'] = $id;

        $this->db->prepare($sql)->execute($data);
    }
}
