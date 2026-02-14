<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model pro práci s auditním logem v admin databázi.
 * Poskytuje filtrování a prohlížení historie změn entit.
 *
 * Audit log uchovává všechny změny auditovaných tabulek
 * (CREATE, UPDATE, DELETE) včetně detailu změn (diff).
 */
final class AuditLogModel extends BaseModel
{
    /**
     * Název tabulky bez prefixu.
     *
     * @var string
     */
    protected string $table = 'audit_logs';

    /**
     * Připojení k admin databázi (centrální audit log pro všechny tenanty).
     *
     * @var string
     */
    protected string $connection = 'admin';

    /**
     * Sloupce, podle kterých lze řadit – pro budoucí implementaci řazení.
     *
     * TODO: [FEATURE] Implementovat dynamické řazení podle zvoleného sloupce
     *
     * @var array
     */
    protected array $orderable = ['id', 'created_at'];

    /**
     * Vyhledá záznamy v audit logu podle zadaných filtrů.
     * Filtry jsou volitelné – metoda použije pouze ty, které jsou vyplněny.
     *
     * Podporované filtry:
     * - user_id  → ID uživatele, který provedl akci
     * - action   → typ akce ('insert', 'update', 'delete')
     * - table    → název entity/tabulky (např. 'users', 'teams')
     * - ip       → IP adresa uživatele
     * - from     → datum od (formát YYYY-MM-DD)
     * - to       → datum do (formát YYYY-MM-DD)
     *
     * Automaticky aplikuje:
     * - Tenant izolaci (company_id)
     * - Řazení od nejnovějších po nejstarší
     * - Omezení počtu výsledků (prevence přetížení)
     *
     * Očekává:
     * - Platný tenant kontext (Auth::companyId())
     * - Pokud není zadán limit, vrací max 100 záznamů
     *
     * TODO: [PERFORMANCE] Přidat stránkování místo pevného LIMIT
     * TODO: [FEATURE] Přidat fulltext vyhledávání v JSON diff
     * TODO: [FEATURE] Přidat možnost exportu do CSV
     *
     * @param array $filters Asociativní pole filtrů
     * @param int $limit Maximální počet vrácených záznamů (výchozí 100)
     * @return array Seznam auditních záznamů
     */
    public function findByFilters(array $filters, int $limit = 100): array
    {
        $where  = $this->applyTenant([]);
        $params = $where;

        // Aplikace volitelných filtrů
        if (!empty($filters['user_id'])) {
            $where['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where['action'] = $filters['action'];
        }

        if (!empty($filters['table'])) {
            $where['entity'] = $filters['table'];
        }

        if (!empty($filters['ip'])) {
            $where['ip_address'] = $filters['ip'];
        }

        $sql = "SELECT * FROM {$this->tableName}";
        $clauses = [];

        // Sestavení WHERE podmínek
        foreach ($where as $col => $val) {
            $clauses[] = "{$col} = :{$col}";
            $params[$col] = $val;
        }

        // Datové rozmezí
        if (!empty($filters['from'])) {
            $clauses[] = 'created_at >= :from';
            $params['from'] = $filters['from'] . ' 00:00:00';
        }

        if (!empty($filters['to'])) {
            $clauses[] = 'created_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }

        if ($clauses) {
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        // Řazení a limit
        $sql .= ' ORDER BY created_at DESC LIMIT ' . (int) $limit;

        return $this->fetchAll($sql, $params);
    }
}