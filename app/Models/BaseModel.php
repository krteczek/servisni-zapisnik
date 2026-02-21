<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\AuditLogCore;
use App\Core\Config;
use App\Core\Auth;
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
     * Očekává:
     * - Potomek definuje protected string $table
     *
     * TODO: [PERFORMANCE] Zvážit caching názvu tabulky napříč requesty
     * TODO: [MAINTENANCE] Přidat kontrolu existence tabulky při vývoji
     *
     * @throws LogicException Pokud $table není definováno
     */
    public function __construct()
    {
        if (!isset($this->table) || $this->table === '') {
            throw new LogicException(
                static::class . ' must define protected string $table'
            );
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
     * TODO: [PERFORMANCE] Přidat caching výsledku na úrovni třídy
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
     * Očekává:
     * - Model je tenant-aware
     * - Uživatel je přihlášen
     *
     * TODO: [SECURITY] Přidat fallback pro CLI prostředí (cron)
     *
     * @return int
     * @throws LogicException Pokud model není tenant-aware nebo chybí kontext
     */
    protected function tenantId(): int
    {
        if (!$this->tenantAware) {
            throw new LogicException('Model is not tenant-aware');
        }

        $companyId = Auth::companyId();

        if (!$companyId) {
            throw new LogicException('Tenant context missing');
        }

        return $companyId;
    }

    /**
     * Aplikuje tenant podmínku na WHERE pole.
     * Pokud je model tenant-aware, přidá company_id = aktuální tenant.
     *
     * @param array $where Vstupní WHERE podmínky
     * @return array WHERE podmínky s tenantem
     */
    protected function applyTenant(array $where): array
    {
        if ($this->tenantAware) {
            $where[$this->tenantColumn] = $this->tenantId();
        }

        return $where;
    }

    /* ==========================================================
     * AUDIT
     * ========================================================== */

    /**
     * Zjistí, zda má být tato operace auditována.
     *
     * Podmínky:
     * - Připojení musí být 'admin' (audit se ukládá do admin DB)
     * - Tabulka musí být v konfiguraci 'audit.auditables'
     * - Tabulka nesmí být v konfiguraci 'audit.ignores'
     *
     * TODO: [FEATURE] Přidat možnost auditovat i work databázi (do samostatné tabulky)
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

        return in_array($this->table, $config['auditables'] ?? [], true);
    }

    /**
     * Vypočítá rozdíl mezi starým a novým stavem entity.
     * Ignoruje systémové sloupce (id, created_at, updated_at, password).
     *
     * TODO: [FEATURE] Přidat možnost konfigurovat ignorované sloupce na úrovni modelu
     *
     * @param array $before Původní data
     * @param array $after Nová data
     * @return array Pole změn ve formátu [field => ['from' => old, 'to' => new]]
     */
    protected function diff(array $before, array $after): array
    {
        $diff = [];

        $ignore = [
            'id',
            'created_at',
            'updated_at',
            'password',
            'password_hash',
        ];

        foreach ($after as $key => $newValue) {
            if (in_array($key, $ignore, true)) {
                continue;
            }

            $oldValue = $before[$key] ?? null;

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
     * @return array Seznam záznamů
     */
    public function all(): array
    {
        $where = $this->applyTenant([]);

        $sql = "SELECT * FROM {$this->tableName}";

        if ($where) {
            $parts = [];
            foreach ($where as $col => $val) {
                $parts[] = "{$col} = :{$col}";
            }

            $sql .= ' WHERE ' . implode(' AND ', $parts);
        }

        $sql .= ' ORDER BY id DESC';

        return $this->fetchAll($sql, $where);
    }

    /**
     * Najde záznam podle ID s tenant izolací.
     *
     * @param int $id ID záznamu
     * @return array|null Data záznamu nebo null
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
//var_dump($sql);
			$ok = $this->fetchOne($sql, $where);
//var_dump($ok);
			
        return $ok;
    }

    /* ==========================================================
     * INSERT
     * ========================================================== */

    /**
     * Vytvoří nový záznam s automatickým doplněním tenant ID.
     * Auditováno pokud je model auditovatelný.
     *
     * TODO: [PERFORMANCE] Batch insert pro více záznamů najednou
     *
     * @param array $data Data k vložení
     * @return int ID nově vytvořeného záznamu
     * @throws LogicException Pokud jsou data prázdná
     */
    public function create(array $data): int
    {
        if ($data === []) {
        	/* TODO: přepsat tak, aby nebyl exception pro uživatele, ale například false a logger mechat udělat záznam pro roota */
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
     * @param array $data Kompletní data k vložení
     * @return int ID nového záznamu
     */
    protected function insertRaw(array $data): int
    {
        $cols   = array_keys($data);
        $fields = implode(', ', $cols);
        $values = ':' . implode(', :', $cols);

        $stmt = $this->db()->prepare(
            "INSERT INTO {$this->tableName} ({$fields}) VALUES ({$values})"
        );
        //var_dump($stmt);exit;
        $stmt->execute($data);

        $lastId = (int) $this->db()->lastInsertId();

        if ($this->shouldAudit()) {
            try {
                AuditLogCore::log(
                    entity: $this->table,
                    entityId: $lastId,
                    action: 'insert',
                    diff: $this->diff([], $data)
                );
            } catch (Throwable) {
                // TODO: [OBSERVABILITY] Lepší logování selhání auditu
                error_log('Audit insert failed: ' . $lastId);
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
     * TODO: [PERFORMANCE] Bulk update pro více záznamů
     *
     * @param int $id ID záznamu k aktualizaci
     * @param array $data Data k aktualizaci (pouze změněné sloupce)
     * @return bool TRUE pokud update proběhl úspěšně
     */
    public function update(int $id, array $data): bool
    {
        if ($data === []) {
            return false;
        }

        $before = $this->find($id);
        if (!$before) {
            return false;
        }

        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "{$key} = :{$key}";
        }

        $params = $data;
        $params['id'] = $id;

        if ($this->tenantAware) {
            $params[$this->tenantColumn] = $this->tenantId();
        }

        $sql = "UPDATE {$this->tableName}
                SET " . implode(', ', $set) . "
                WHERE id = :id";

        if ($this->tenantAware) {
            $sql .= " AND {$this->tenantColumn} = :{$this->tenantColumn}";
        }

        $stmt = $this->db()->prepare($sql);
        $ok   = $stmt->execute($params);

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
            } catch (Throwable) {
                error_log('Audit update failed: ' . $id);
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
     * @param string $sql SQL dotaz s placeholdery
     * @param array $params Parametry pro prepared statement
     * @return array Výsledek dotazu
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
	//var_dump($sql);
	//var_dump($params);exit;

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Provede SQL dotaz a vrátí první řádek.
     *
     * @param string $sql SQL dotaz s placeholdery
     * @param array $params Parametry pro prepared statement
     * @return array|null První řádek nebo null
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
     * @return array Data záznamu
     * @throws LogicException Pokud záznam neexistuje
     */
    public function findOrFail(int $id): array
    {
        $row = $this->find($id);

        if (!$row) {
            throw new LogicException('Record not found');
        }

        return $row;
    }

    /**
     * Vygeneruje SQL podmínku pro tenant izolaci v JOIN dotazech.
     *
     * TODO: [MAINTENANCE] Přidat podporu pro různé aliasy
     *
     * @param string $alias Alias tabulky v dotazu
     * @return string SQL podmínka (např. " AND company_id = :company_id")
     */
    protected function tenantWhere(string $alias = ''): string
    {
        if (!$this->tenantAware) {
            return '';
        }

        $col = $alias ? $alias . '.' . $this->tenantColumn : $this->tenantColumn;

        return " AND {$col} = :" . $this->tenantColumn;
    }
    
    public function createWithTenant(int $tenantId, array $data): int
{
    if ($data === []) {
        throw new LogicException('CreateWithTenant: empty data');
    }

    if ($this->tenantAware) {
        $data[$this->tenantColumn] = $tenantId;
    }

    return $this->insertRaw($data);
}
}