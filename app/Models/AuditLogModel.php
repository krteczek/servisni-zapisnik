<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Types;

/**
 * Model pro práci s auditním logem konkrétní firmy v admin DB.
 *
 * Auditní log je uložen v jedné globální tabulce,
 * ale z pohledu aplikace je tenant-aware.
 * Každé čtení je omezeno na aktuální tenant.
 */
/** @phpstan-import-type AuditLogRow from Types */

final class AuditLogModel extends BaseModel
{
    protected string $table = 'audit_logs';
    protected string $connection = 'admin';
    
    /**
     * ❗ DŮLEŽITÉ
     * Audit log JE tenant-aware!!! slouži adminovi tenantu ke zkoumání, co se dělo v jeho části systému     * 
     */
    protected bool $tenantAware = true;

    /**
     * Uloží auditní záznam do log souboru na disku a do DB.
     *
     * @param array<string, mixed> $data
     * @return int ID záznamu
     */
    public function insertLog(array $data): int
    {
    	// error_log('AUDIT INSERT: ' . json_encode($data));
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
     * - user_agent
     * - from (YYYY-MM-DD)
     * - to   (YYYY-MM-DD)
     * @param array{
     *     user_id: ?string,
     *     action: ?string,
     *     entity: ?string,
     *     from: ?string,
     *     to: ?string,
     *     ip: ?string,
     *     user_agent: ?string
     * } $filters
     * @return array<int, AuditLogRow>
    */
    public function findByFilters(array $filters, int $limit = 100): array
    {
        $where  = [];
        $params = [];

        if (($filters['user_id'] ?? '') !== '') {
            $where['user_id'] = (int) $filters['user_id'];
        }

        if (($filters['action'] ?? '') !== '') {
            $where['action'] = $filters['action'];
        }

        if (($filters['entity'] ?? '') !== '') {
            $where['entity'] = $filters['entity'];
        }

        if (($filters['ip'] ?? '') !== '') {
            $where['ip_address'] = $filters['ip'];
        }

        if (($filters['user_agent'] ?? '') !== '') {
            $where['user_agent'] = $filters['user_agent'];
        }

        $where['company_id'] = $this->tenantId();
        $sql = "SELECT * FROM {$this->tableName}";
        $clauses = [];

        foreach ($where as $col => $val) {
            $clauses[] = "{$col} = :{$col}";
            $params[$col] = $val;
        }

        if (($filters['from'] ?? '') !== '') {
            $clauses[] = 'created_at >= :from';
            $params['from'] = $filters['from'] . ' 00:00:00';
        }

        if (($filters['to'] ?? '') !== '') {
            $clauses[] = 'created_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }

        $sql .= ' WHERE ' . implode(' AND ', $clauses);

        $sql .= ' ORDER BY created_at DESC LIMIT ' . $limit;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Najde poslední záznamy pro konkrétní entitu.
     * 
     * @param string $entity
     * @param int|string $entityId
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function findByEntity(string $entity, int|string $entityId, int $limit = 20): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE company_id = :company_id
                AND entity = :entity
                AND entity_id = :entity_id
            ORDER BY created_at DESC
            LIMIT " . $limit;

        return $this->fetchAll($sql, [
            'company_id' => $this->tenantId(),
            'entity'    => $entity,
            'entity_id' => $entityId,
        ]);
    }

    /**
     * Najde poslední akce uživatele.
     *
     * @param int $userId
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function findByUser(int $userId, int $limit = 50): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE company_id = :company_id
                AND user_id = :user_id
            ORDER BY created_at DESC
            LIMIT " . $limit;

        return $this->fetchAll($sql, [
            'company_id' => $this->tenantId(),
            'user_id' => $userId,
        ]);
    }

 }