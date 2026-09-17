<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\AuditLogCore;
use App\Core\Config;
use App\Core\Auth;
use App\Core\TenantContext;
use App\Core\LoggerHolder;
use App\Models\WorkOrderSequencesModel;
use PDO;
use LogicException;
use Throwable;

/**
 * Abstraktní základní model pro všechny databázové modely.
 * Poskytuje společné CRUD operace, multi-tenant izolaci a audit logování.
 *
 * Každý potomek MUSÍ definovat vlastnost $table s názvem tabulky (bez prefixu).
 * Volitelně může přepsat $connection, $tenantAware, $tenantColumn.
 */
abstract class BaseModel
{
    /**
     * @var PDO|null PDO připojení (lazy-loaded)
     */
    protected ?PDO $db = null;

    /**
     * Název tabulky BEZ databázového prefixu.
     * Musí být definováno v potomkovi.
     *
     * @var string
     */
    protected string $table;

    /**
     * Finální název tabulky včetně prefixu z konfigurace.
     * Sestavuje se automaticky v konstruktoru.
     *
     * @var string
     */
    protected string $tableName;

    /**
     * Typ databázového připojení: 'admin' | 'work'
     *
     * @var string
     */
    protected string $connection = 'admin';

    /**
     * Určuje, zda je model tenant-aware (izolovaný podle company_id).
     * FALSE pro globální tabulky (companies, audit_logs, atd.)
     *
     * @var bool
     */
    protected bool $tenantAware = true;

    /**
     * Název sloupce pro identifikaci tenanta.
     *
     * @var string
     */
    protected string $tenantColumn = 'company_id';


    /**
     * Inicializuje model a sestaví finální název tabulky.
     *
     * @param PDO|null $pdo
     * @throws LogicException Pokud $table není definováno
     */
    public function __construct(?PDO $pdo = null)
    {
        if (!isset($this->table) || $this->table === '') {
            throw new LogicException(
                static::class . ' must define protected string $table'
            );
        }

        if ($pdo !== null) {
            $this->db = $pdo;
        }

        $this->tableName = $this->resolveTableName();
    }


    /* ==========================================================
     * DB
     * ========================================================== */

    /**
     * Vybere správné PDO připojení podle typu.
     *
     * @return PDO
     * @throws LogicException Při neznámém typu připojení
     */
    protected function resolveDb(): PDO
    {
        return match ($this->connection) {
            'admin' => Database::admin(),
            'work'  => Database::work(),
            default => throw new LogicException(
                'Unknown DB connection: ' . $this->connection
            ),
        };
    }

    /**
     * Vrátí PDO připojení s lazy loading.
     *
     * @return PDO
     */
    protected function db(): PDO
    {
        if ($this->db === null) {
            $this->db = $this->resolveDb();
        }

        return $this->db;
    }

    /**
     * Sestaví finální název tabulky s prefixem z konfigurace.
     *
     * @return string
     */
    protected function resolveTableName(): string
    {
        $prefix = (string) Config::get('database.prefix', '');
        return $prefix . $this->table;
    }

    /* ==========================================================
     * TENANT
     * ========================================================== */

    /**
     * Vrátí ID aktuálního tenanta z přihlášeného uživatele.
     *
     * @return int
     * @throws LogicException Pokud model není tenant-aware nebo chybí kontext
     */
    protected function tenantId(): int
    {
        if (!$this->tenantAware) {
            throw new LogicException('Model is not tenant-aware');
        }

        $companyId = TenantContext::get() ?? Auth::companyId();

        if ($companyId === null) {
            throw new LogicException('Tenant context missing');
        }
        return $companyId;
    }

    /**
     * Aplikuje tenant podmínku na WHERE pole.
     * Pokud je model tenant-aware, přidá company_id = aktuální tenant.
     *
     * @param array<string, mixed> $where Vstupní WHERE podmínky
     * @return array<string, mixed> WHERE podmínky s tenantem
     * @throws LogicException Pokud je tenant column nastaven ručně
     */
    protected function applyTenant(array $where): array
    {
        if (!$this->tenantAware) {
            return $where;
        }

        $tenantId = $this->tenantId();

        // ❗ zakázat ruční company_id
        if (array_key_exists($this->tenantColumn, $where)) {
            throw new LogicException(
                "Manual {$this->tenantColumn} condition is not allowed on tenant-aware model."
            );
        }

        $where[$this->tenantColumn] = $tenantId;

        return $where;
    }


    /**
     * Nastaví PDO připojení (pro testy).
     *
     * @param PDO $pdo
     * @return void
     */
    public function setConnection(PDO $pdo): void
    {
        $this->db = $pdo;
    }


    /* ==========================================================
     * AUDIT
     * ========================================================== */

    /**
     * Zjistí, zda má být tato operace auditována.
     *
     * @return bool
     */
    protected function shouldAudit(): bool
    {
        if ($this->connection !== 'admin') {
            return false;
        }

        $config = Config::get('audit');

        if (in_array($this->table, $config['ignores'] ?? [], true)) {
            return false;
        }
        $result = in_array($this->table, $config['auditables'] ?? [], true);

        return $result;
    }

    /**
     * Vypočítá rozdíl mezi starým a novým stavem entity.
     * Ignoruje systémové sloupce (id, created_at, updated_at, password).
     *
     * @param array<string, mixed> $before Původní data
     * @param array<string, mixed> $after Nová data
     * @return array<string, array{from: mixed, to: mixed}> Pole změn
     */
    protected function diff(array $before, array $after): array
    {
        $diff = [];

        $ignore = [
            'id' => true,
            'created_at' => true,
            'updated_at' => true,
            'password' => true,
            'password_hash' => true,
        ];

        foreach ($after as $key => $newValue) {

            if (isset($ignore[$key])) {
                continue;
            }

            $oldValue = $before[$key] ?? null;

            // pole / JSON
            if (is_array($oldValue) || is_array($newValue)) {
                if (json_encode($oldValue) === json_encode($newValue)) {
                    continue;
                }
            }

            if ($oldValue !== $newValue) {
                $diff[$key] = [
                    'from' => $oldValue,
                    'to'   => $newValue,
                ];
            }
        }

        return $diff;
    }

    /* ==========================================================
     * SELECT
     * ========================================================== */

    /**
     * Vrátí všechny záznamy z tabulky s tenant izolací.
     * Řazeno sestupně podle ID.
     *
     * @return array<int, array<string, mixed>> Seznam záznamů
     */
    public function all(): array
    {
        try {
            $where = $this->applyTenant([]);

            $sql = "SELECT * FROM {$this->tableName}";

            if ($where !== []) {
                $parts = [];
                foreach ($where as $col => $val) {
                    $parts[] = "{$col} = :{$col}";
                }

                $sql .= ' WHERE ' . implode(' AND ', $parts);
            }

            $sql .= ' ORDER BY id DESC';

            return $this->fetchAll($sql, $where);
        } catch (Throwable $e) {
            LoggerHolder::get()->error('BaseModel.all() failed', [
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Najde záznam podle ID s tenant izolací.
     *
     * @param int $id ID záznamu
     * @return array<string, mixed>|null Data záznamu nebo null
     */
    public function find(int $id): ?array
    {
        $where = $this->applyTenant(['id' => $id]);

        $parts = [];
        foreach ($where as $col => $val) {
            $parts[] = "{$col} = :{$col}";
        }

        $sql = "SELECT * FROM {$this->tableName}
                WHERE " . implode(' AND ', $parts) . "
                LIMIT 1";

        $ok = $this->fetchOne($sql, $where);

        return $ok;
    }

    /* ==========================================================
     * INSERT
     * ========================================================== */

    /**
     * Vytvoří nový záznam s automatickým doplněním tenant ID.
     * Auditováno pokud je model auditovatelný.
     *
     * @param array<string, mixed> $data Data k vložení
     * @return int ID nově vytvořeného záznamu
     * @throws LogicException Pokud jsou data prázdná
     */
    public function create(array $data): int
    {
        if (isset($data[$this->tenantColumn])) {
            throw new LogicException("Cannot set tenant column manually. Use tenant-aware model.");
        }

        if ($data === []) {
            throw new LogicException('Create: empty data');
        }

        if ($this->tenantAware) {
            $data[$this->tenantColumn] = $this->tenantId();
        }

        return $this->insertRaw($data);
    }

    /**
     * Nízkoúrovňový INSERT bez tenant logiky.
     * Používá se pro modely, které nejsou tenant-aware nebo pro importy.
     *
     * @param array<string, mixed> $data Kompletní data k vložení
     * @return int ID nového záznamu
     * @throws Throwable Při selhání insertu
     */
    protected function insertRaw(array $data): int
    {
        $lastId = 0;
        $sql = null;

        try {
            $cols   = array_keys($data);
            $fields = implode(', ', $cols);
            $values = ':' . implode(', :', $cols);


            $sql = "INSERT INTO {$this->tableName} ({$fields}) VALUES ({$values})";
            $stmt = $this->db()->prepare($sql);

            $stmt->execute($data);

            $lastId = (int) $this->db()->lastInsertId();
        } catch (Throwable $e) {
            LoggerHolder::get()->error('BaseModel.insertRaw: failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'data'    => json_encode($data),
                'sql'     => $sql,
            ]);
            throw $e; // 🔥 KRITICKÉ
        }

        if ($this->shouldAudit()) {
            try {
                AuditLogCore::log(
                    entity: $this->table,
                    entityId: $lastId,
                    action: 'insert',
                    diff: $this->diff([], $data)
                );
            } catch (Throwable $e) {
                LoggerHolder::get()->error('BaseModel.auditInsertRaw failed', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                    'data'    => json_encode($data),
                ]);
            }
        }

        return $lastId;
    }

    /* ==========================================================
     * UPDATE
     * ========================================================== */

    /**
     * Aktualizuje existující záznam.
     * Automaticky aplikuje tenant izolaci a audit.
     *
     * @param int $id ID záznamu k aktualizaci
     * @param array<string, mixed> $data Data k aktualizaci
     * @return bool TRUE pokud update proběhl úspěšně
     * @throws Throwable Při selhání update
     */
    public function update(int $id, array $data): bool
    {
        if ($data === []) {
            return false;
        }

        $before = $this->find($id);
        if ($before === null) {
            return false;
        }
        $ok = null;
        $sql = null;
        try {
            $set = [];
            foreach ($data as $key => $val) {
                $set[] = "{$key} = :{$key}";
            }

            $params = $data;
            $params['id'] = $id;

            if ($this->tenantAware) {
                $params[$this->tenantColumn] = $this->tenantId();
            }

            if (isset($data[$this->tenantColumn])) {
                throw new LogicException("Cannot modify tenant column.");
            }

            $sql = "UPDATE {$this->tableName}
                    SET " . implode(', ', $set) . "
                    WHERE id = :id";

            if ($this->tenantAware) {
                $sql .= " AND {$this->tenantColumn} = :{$this->tenantColumn}";
            }

            $stmt = $this->db()->prepare($sql);

            $ok   = $stmt->execute($params);

        } catch (Throwable $e) {
            LoggerHolder::get()->error('BaseModel.update failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'data'    => json_encode($data),
                'id'      => $id,
                'sql'     => $sql,
                'entity'  => $this->table,
            ]);
            throw $e; // 🔥 KRITICKÉ
        }
        if ($ok && $this->shouldAudit()) {
            try {
                $diff = $this->diff($before, $data);

                if ($diff !== []) {
                    AuditLogCore::log(
                        entity: $this->table,
                        entityId: $id,
                        action: 'update',
                        diff: $diff
                    );
                }
            } catch (Throwable $e) {
                LoggerHolder::get()->error('BaseModel.auditUpdate failed', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                    'data'    => json_encode($data),
                    'entity' => $this->table,
                    'entity_id' => $id,
                ]);
            }
        }

        return $ok;
    }

    /* ==========================================================
     * INTERNAL HELPERS
     * ========================================================== */

    /**
     * Provede SQL dotaz a vrátí všechny řádky.
     *
     * @param array<int|string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Provede SQL dotaz a vrátí první řádek.
     *
     * @param string $sql SQL dotaz s placeholdery
     * @param array<int|string, mixed> $params
     * @return array<string, mixed>|null     
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * Najde záznam podle ID nebo vyhodí výjimku.
     *
     * @param int $id ID záznamu
     * @return array<string, mixed> Data záznamu
     * @throws LogicException Pokud záznam neexistuje
     */
    public function findOrFail(int $id): array
    {
        $row = $this->find($id);

        if ($row === null) {
            throw new LogicException('Record not found');
        }

        return $row;
    }

    /**
     * Vygeneruje SQL podmínku pro tenant izolaci v JOIN dotazech.
     *
     * @param string $alias Alias tabulky v dotazu
     * @return string SQL podmínka
     */
    protected function tenantWhereOLD(string $alias = ''): string
    {
        if (!$this->tenantAware) {
            return '';
        }

        $col = $alias !== '' ? $alias . '.' . $this->tenantColumn : $this->tenantColumn;
        return " AND {$col} = :" . $this->tenantColumn;
    }

    /**
     * Vytvoří nový záznam s explicitním tenant ID.
     *
     * @param int $tenantId
     * @param array<string, mixed> $data
     * @return int
     */
    public function createWithTenant(int $tenantId, array $data): int
    {
        if ($this->tenantAware) {
            $data[$this->tenantColumn] = $tenantId;
        }

        return $this->insertRaw($data);
    }


    /**
     * Najde první záznam podle sloupce a hodnoty.
     *
     * @param string $column
     * @param mixed $value
     * @return array<string, mixed>|null
     */
    protected function firstWhere(string $column, mixed $value): ?array
    {
        $where = $this->applyTenant([$column => $value]);

        $parts = [];
        foreach ($where as $col => $val) {
            $parts[] = "{$col} = :{$col}";
        }

        $sql = "SELECT * FROM {$this->tableName}
                WHERE " . implode(' AND ', $parts) . "
                LIMIT 1";

        return $this->fetchOne($sql, $where);
    }


    /**
     * Aktualizuje první záznam podle sloupce a hodnoty.
     *
     * @param string $column
     * @param mixed $value
     * @param array<string, mixed> $data
     * @return bool
     * @throws Throwable
     */
    protected function updateWhere(string $column, mixed $value, array $data): bool
    {
        if ($data === []) {
            return false;
        }

        if (isset($data['id'])) {
            throw new LogicException('Cannot modify primary key.');
        }

        if (isset($data[$this->tenantColumn])) {
            throw new LogicException("Cannot modify tenant column.");
        }

        $sql = null;
        $ok = null;
        $before = $this->firstWhere($column, $value);

        if ($before === null) {
            return false;
        }

        try {
            $where = $this->applyTenant([$column => $value]);

            $set = [];
            foreach ($data as $key => $val) {
                $set[] = "{$key} = :set_{$key}";
            }

            $params = [];
            foreach ($data as $key => $val) {
                $params["set_{$key}"] = $val;
            }

            foreach ($where as $key => $val) {
                $params[$key] = $val;
            }

            $parts = [];
            foreach ($where as $col => $val) {
                $parts[] = "{$col} = :{$col}";
            }

            $sql = "UPDATE {$this->tableName}
                    SET " . implode(', ', $set) . "
                    WHERE " . implode(' AND ', $parts);

            $stmt = $this->db()->prepare($sql);

            $ok = $stmt->execute($params);
        } catch (Throwable $e) {
            LoggerHolder::get()->error('BaseModel.updateWhere failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'data'    => json_encode($data),
                'value'   => $value,
                'column'  => $column,
                'sql'     => $sql,
                'entity'  => $this->table,
            ]);
            throw $e;
        }

        if ($ok && $this->shouldAudit()) {
            try {
                $diff = $this->diff($before, $data);

                if ($diff !== []) {
                    AuditLogCore::log(
                        entity: $this->table,
                        entityId: (int)$before['id'],
                        action: 'update',
                        diff: $diff
                    );
                }
            } catch (Throwable $e) {
                LoggerHolder::get()->error('BaseModel.auditUpdate failed', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                    'data'    => json_encode($data),
                    'entity' => $this->table,
                ]);
            }
        }
        return $ok;
    }

    /**
     * @return bool
     */
    protected function isTenantAware(): bool
    {
        return $this->tenantAware;
    }

    /**
     * Výběr z DB podle statusu.
     *
     * @param array<int, string> $statuses
     * @param string $orderBy
     * @param string $direction
     * @return array<int, array<string, mixed>>
     */
    public function whereStatus(
        array $statuses,
        string $orderBy = 'updated_at',
        string $direction = 'DESC'
    ): array {
        if ($statuses === []) {
            return [];
        }

        $allowedColumns = ['updated_at', 'created_at', 'title', 'status'];
        $allowedDirections = ['ASC', 'DESC'];

        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'updated_at';
        }

        if (!in_array(strtoupper($direction), $allowedDirections, true)) {
            $direction = 'DESC';
        }

        $in = implode(',', array_fill(0, count($statuses), '?'));

        $sql = "SELECT * FROM {$this->tableName}
                WHERE status IN ($in)";

        $params = $statuses;

        if ($this->tenantAware) {
            $sql .= " AND {$this->tenantColumn} = ?";
            $params[] = $this->tenantId();
        }

        $sql .= " ORDER BY $orderBy $direction";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Přidá do SQL podmínky vyhledávání podle sloupce.
     *
     * @param string $column
     * @param string $q
     * @param array<int, mixed> $params
     * @return string
     */
    protected function buildSearchCondition(string $column, string $q, array &$params): string
    {
        if ($q === '') {
            return '';
        }

        $params[] = '%' . $q . '%';
        return " AND {$column} LIKE ?";
    }
}