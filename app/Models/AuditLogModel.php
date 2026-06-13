<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model pro práci s auditním logem (admin DB, globální tabulka).
 *
 * ⚠️ NENÍ tenant-aware:
 * - audit log obsahuje data pro všechny tenanty
 * - tenant filtr se aplikuje RUČNĚ přes filtry
 */
final class AuditLogModel extends BaseModel
{
    protected string $table = 'audit_logs';
    protected string $connection = 'admin';
    
    /**
     * ❗ DŮLEŽITÉ
     * Audit log není tenant-aware → jinak by padal mimo Auth kontext
     */
    protected bool $tenantAware = false;

    /**
     * Uloží auditní záznam.
     *
     * @param array $data
     * @return int ID záznamu
     */
    public function insertLog(array $data): int
    {
    	error_log('AUDIT INSERT: ' . json_encode($data));
        return $this->insertRaw($data);
    }

    /**
     * Vyhledá auditní záznamy podle filtrů.
     *
     * Podporované filtry:
     * - company_id
     * - user_id
     * - action
     * - entity
     * - ip
     * - from (YYYY-MM-DD)
     * - to   (YYYY-MM-DD)
     *
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public function findByFilters(array $filters, int $limit = 100): array
    {
        $where  = [];
        $params = [];

        // 🔒 tenant filtr (RUČNĚ!)
        if (!empty($filters['company_id'])) {
            $where['company_id'] = (int)$filters['company_id'];
        }

        if (!empty($filters['user_id'])) {
            $where['user_id'] = (int)$filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where['action'] = $filters['action'];
        }

        if (!empty($filters['entity'])) {
            $where['entity'] = $filters['entity'];
        }

        if (!empty($filters['ip'])) {
            $where['ip_address'] = $filters['ip'];
        }

        if (!empty($filters['user_agent'])) {
           $where['user_agent'] = $filters['user_agent'];

        }
        $sql = "SELECT * FROM {$this->tableName}";
        $clauses = [];

        // WHERE podmínky
        foreach ($where as $col => $val) {
            $clauses[] = "{$col} = :{$col}";
            $params[$col] = $val;
        }

        // datum od
        if (!empty($filters['from'])) {
            $clauses[] = 'created_at >= :from';
            $params['from'] = $filters['from'] . ' 00:00:00';
        }

        // datum do
        if (!empty($filters['to'])) {
            $clauses[] = 'created_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }

        if ($clauses) {
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Najde poslední záznamy pro konkrétní entitu.
     *
     * @param string $entity
     * @param int|string $entityId
     * @param int $limit
     * @return array
     */
    public function findByEntity(string $entity, int|string $entityId, int $limit = 20): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE entity = :entity
              AND entity_id = :entity_id
            ORDER BY created_at DESC
            LIMIT " . (int)$limit;

        return $this->fetchAll($sql, [
            'entity'    => $entity,
            'entity_id' => $entityId,
        ]);
    }

    /**
     * Najde poslední akce uživatele.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function findByUser(int $userId, int $limit = 50): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT " . (int)$limit;

        return $this->fetchAll($sql, [
            'user_id' => $userId,
        ]);
    }

    /**
     * Najde poslední záznamy pro tenant (company).
     *
     * @param int $companyId
     * @param int $limit
     * @return array
     */
    public function findByCompany(int $companyId, int $limit = 100): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE company_id = :company_id
            ORDER BY created_at DESC
            LIMIT " . (int)$limit;

        return $this->fetchAll($sql, [
            'company_id' => $companyId,
        ]);
    }
}