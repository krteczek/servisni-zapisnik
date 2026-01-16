<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use LogicException;

final class UserModel
{
    protected PDO $db;

    /** název tabulky BEZ prefixu */
    protected string $table = 'users';

    /** finální název tabulky S prefixem */
    protected string $tableName;

    /** povolené sloupce pro ORDER BY */
    protected array $orderable = ['id'];
    

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
     * ZÁKLADNÍ SELECT – univerzální
     * ========================================================== */

    public function select(
        array|string $columns = '*',
        array $where = [],
        ?string $orderBy = 'id',
        string $direction = 'ASC',
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sqlCols = is_array($columns)
            ? implode(', ', $columns)
            : $columns;

        $sql = "SELECT {$sqlCols} FROM {$this->tableName}";
        $params = [];

        if ($where) {
            $conds = [];
            foreach ($where as $key => $value) {
                $conds[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conds);
        }

        if ($orderBy && in_array($orderBy, $this->orderable, true)) {
            $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY {$orderBy} {$dir}";
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        if ($offset !== null) {
            $sql .= ' OFFSET ' . (int)$offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /* ==========================================================
     * KRATŠÍ ALIASY (čitelnost v controlleru)
     * ========================================================== */

    public function all(string $orderBy = 'id'): array
    {
        return $this->select('*', [], $orderBy);
    }

    public function find(int $id): ?array
    {
        $rows = $this->select('*', ['id' => $id], null, limit: 1);
			//var_dump($rows);exit;

        return $rows[0] ?? null;
    }

    public function emailExists(string $email): bool
    {
        $rows = $this->select('1', ['email' => $email], null, limit: 1);
        return (BOOL) $rows;
    }
    
    public function employeeNumberExists($number): bool
    {
        $rows = $this->select('1', ['employee_number' => $number], null, limit: 1);
        return (BOOL) $rows;
    }
    

    /* ==========================================================
     * INSERT / UPDATE
     * ========================================================== */

    public function insert(array $data): int
    {
        if (!$data) {
            throw new LogicException('Insert data cannot be empty');
        }

        $cols = array_keys($data);
        $fields = implode(', ', $cols);
        $values = ':' . implode(', :', $cols);

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->tableName} ({$fields}) VALUES ({$values})"
        );
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        if (!$data) {
            return;
        }

        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$this->tableName}
                SET " . implode(', ', $set) . "
                WHERE id = :id";

        $data['id'] = $id;

        $this->db->prepare($sql)->execute($data);
    }

    /* ==========================================================
     * DELETE (volitelné, ale hodí se)
     * ========================================================== */

    protected function delete(int $id): void
    {
        $this->db
            ->prepare("DELETE FROM {$this->tableName} WHERE id = :id")
            ->execute(['id' => $id]);
    }
}
