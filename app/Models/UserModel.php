<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use LogicException;
use App\Core\Auth;

final class UserModel extends BaseModel
{
    /** název tabulky BEZ prefixu */
    protected string $table = 'users';

    /**
     * Povolené sloupce pro ORDER BY
     */
    protected array $orderable = ['id'];

    /* ==========================================================
     * ZÁKLADNÍ SELECT – univerzální (multiweb-safe)
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

        // povinný tenant filtr
        $where['company_id'] = Auth::companyId();

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
        return $this->select('*', [], $orderBy);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM {$this->tableName}
             WHERE id = :id
               AND company_id = :company_id
             LIMIT 1"
        );

        $stmt->execute([
            'id'         => $id,
            'company_id' => Auth::companyId(),
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
     * VALIDACE / EXISTENCE
     * ========================================================== */

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = "SELECT 1
                FROM {$this->tableName}
                WHERE email = :email
                  AND company_id = :company_id";

        $params = [
            'email'      => $email,
            'company_id' => Auth::companyId(),
        ];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function employeeNumberExists(string $employeeNumber, int $ignoreUserId = 0): bool
    {
        $sql = "
            SELECT 1
            FROM {$this->tableName}
            WHERE employee_number = :num
              AND company_id = :company_id
        ";

        $params = [
            'num'        => $employeeNumber,
            'company_id' => Auth::companyId(),
        ];

        if ($ignoreUserId > 0) {
            $sql .= " AND id != :ignore";
            $params['ignore'] = $ignoreUserId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM {$this->tableName}
             WHERE email = :email
               AND company_id = :company_id
             LIMIT 1"
        );

        $stmt->execute([
            'email'      => $email,
            'company_id' => Auth::companyId(),
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByEmailAndCompany(string $email, int $companyId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM {$this->tableName}
             WHERE email = :email
               AND company_id = :company_id
               AND active = 1
             LIMIT 1"
        );

        $stmt->execute([
            'email'      => $email,
            'company_id' => $companyId,
        ]);

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

        $data['company_id'] = Auth::companyId();

        return parent::insert($data);
    }

    public function update(int $id, array $data): bool
    {
        if (!$data) {
            return false;
        }
			return parent::updateRow( $id, $data);
        
        /*return parent::updateWhere(
            [
                'id'         => $id,
                'company_id' => Auth::companyId(),
            ],
            $data
        );*/
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
     * TEAMY
     * ========================================================== */

    public function availableForTeam(int $teamId): array
    {
        $teamMembershipsTable = str_replace(
            $this->table,
            'team_memberships',
            $this->tableName
        );

        $sql = "
            SELECT u.*
            FROM {$this->tableName} u
            WHERE u.company_id = :company_id
              AND u.id NOT IN (
                  SELECT tm.user_id
                  FROM {$teamMembershipsTable} tm
                  WHERE tm.team_id = :team_id
                    AND tm.valid_to IS NULL
              )
            ORDER BY u.last_name, u.first_name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'team_id'    => $teamId,
            'company_id' => Auth::companyId(),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ==========================================================
     * DELETE
     * ========================================================== */

    protected function delete(int $id): void
    {
        $this->db
            ->prepare(
                "DELETE FROM {$this->tableName}
                 WHERE id = :id
                   AND company_id = :company_id"
            )
            ->execute([
                'id'         => $id,
                'company_id' => Auth::companyId(),
            ]);
    }
}
