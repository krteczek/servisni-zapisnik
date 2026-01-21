<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use LogicException;

final class UserModel extends BaseModel
{
    /** název tabulky BEZ prefixu */
    protected string $table = 'users';

    /**
     * Povolené sloupce pro ORDER BY
     */
    protected array $orderable = ['id'];

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
            $sql .= ' LIMIT ' . (int) $limit;
        }

        if ($offset !== null) {
            $sql .= ' OFFSET ' . (int) $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ==========================================================
     * KRATŠÍ ALIASY
     * ========================================================== */

    public function all(string $orderBy = 'id'): array
    {
        return $this->allRows($orderBy);
    }

    public function find(int $id): ?array
    {
        return $this->findRow($id);
    }

    /* ==========================================================
     * VALIDACE / EXISTENCE
     * ========================================================== */

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->tableName} WHERE email = :email";
        $params = ['email' => $email];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function employeeNumberExists(string $number, ?int $ignoreId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->tableName} WHERE employee_number = :num";
        $params = ['num' => $number];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName} WHERE email = :email LIMIT 1"
        );

        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
     * INSERT / UPDATE
     * ========================================================== */

    public function insert(array $data): int
    {
        if (!$data) {
            throw new LogicException('Insert data cannot be empty');
        }

        return parent::insert($data);
    }

    public function update(int $id, array $data): void
    {
        if (!$data) {
            return;
        }

        parent::updateRow($id, $data);
    }

    /* ==========================================================
     * STAVY
     * ========================================================== */

    public function active(): array
    {
        return $this->select('*', ['active' => 1]);
    }

    public function inactive(): array
    {
        return $this->select('*', ['active' => 0]);
    }

    /* ==========================================================
     * DELETE
     * ========================================================== */

    // TODO: místo DELETE použít soft delete (active = 0)
    protected function delete(int $id): void
    {
        $this->db
            ->prepare("DELETE FROM {$this->tableName} WHERE id = :id")
            ->execute(['id' => $id]);
    }
}
