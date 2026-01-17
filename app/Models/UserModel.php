<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use LogicException;

/**
 * UserModel
 *
 * Model pro práci s uživateli.
 * Zodpovídá výhradně za přístup k tabulce `users`
 * a veškeré CRUD operace nad uživatelskými daty.
 *
 * Neřeší autorizaci ani aplikační logiku.
 * Vrací čistá data ve formě asociativních polí.
 */
final class UserModel
{
    /**
     * PDO instance databázového spojení
     */
    protected PDO $db;

    /**
     * Název tabulky bez prefixu
     */
    protected string $table = 'users';

    /**
     * Finální název tabulky včetně prefixu
     */
    protected string $tableName;

    /**
     * Povolené sloupce pro ORDER BY
     * (ochrana proti SQL injection)
     */
    protected array $orderable = ['id'];

    /**
     * Inicializace modelu a databázového spojení
     *
     * @throws LogicException pokud není definován název tabulky
     */
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

    /**
     * Univerzální SELECT dotaz
     *
     * @param array|string $columns Sloupce k výběru
     * @param array $where Asociativní pole WHERE podmínek
     * @param string|null $orderBy Sloupec pro řazení
     * @param string $direction Směr řazení ASC|DESC
     * @param int|null $limit LIMIT
     * @param int|null $offset OFFSET
     *
     * @return array Pole nalezených řádků
     */
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

        return $stmt->fetchAll();
    }

    /* ==========================================================
     * KRATŠÍ ALIASY
     * ========================================================== */

    /**
     * Vrátí všechny uživatele
     *
     * @param string $orderBy Sloupec pro řazení
     * @return array
     */
    public function all(string $orderBy = 'id'): array
    {
        return $this->select('*', [], $orderBy);
    }

    /**
     * Najde uživatele podle ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $rows = $this->select('*', ['id' => $id], null, limit: 1);
        return $rows[0] ?? null;
    }

    /**
     * Ověří existenci emailu
     *
     * @param string $email
     * @param int|null $ignoreId ID uživatele, který se má ignorovat
     * @return bool
     */
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

    /**
     * Ověří existenci osobního čísla
     *
     * @param string $number
     * @param int|null $ignoreId ID uživatele, který se má ignorovat
     * @return bool
     */
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

    /**
     * Najde uživatele podle emailu
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM {$this->tableName}
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
     * INSERT / UPDATE
     * ========================================================== */

    /**
     * Vloží nového uživatele
     *
     * @param array $data
     * @return int ID nově vloženého záznamu
     *
     * @throws LogicException pokud jsou data prázdná
     */
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

        return (int) $this->db->lastInsertId();
    }

    /**
     * Aktualizuje uživatele
     *
     * @param int $id
     * @param array $data
     * @return void
     */
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
     * DELETE
     * ========================================================== */

    /**
     * Smaže uživatele podle ID
     *
     * @param int $id
     * @return void
     */
    protected function delete(int $id): void
    {
        $this->db
            ->prepare("DELETE FROM {$this->tableName} WHERE id = :id")
            ->execute(['id' => $id]);
    }
}