<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AuditLogModel
{
    private PDO $db;
    private string $table;

    public function __construct()
    {
        $this->db = Database::pdo();
        $this->table = Database::table('audit_log');
    }

    /**
     * Zapíše auditní událost
     */
    public function log(array $data): void
    {
        $sql = "
            INSERT INTO {$this->table}
            (
                user_id,
                action,
                entity,
                entity_id,
                old_data,
                new_data,
                ip_address,
                user_agent,
                created_at
            )
            VALUES
            (
                :user_id,
                :action,
                :entity,
                :entity_id,
                :old_data,
                :new_data,
                :ip_address,
                :user_agent,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'    => $data['user_id'],
            'action'     => $data['action'],
            'entity'     => $data['entity'],
            'entity_id'  => $data['entity_id'] ?? null,
            'old_data'   => $data['old_data'] ?? null,
            'new_data'   => $data['new_data'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }

    /**
     * Základní výpis logů (administrace)
     */
    public function getAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['entity'])) {
            $where[] = 'entity = :entity';
            $params['entity'] = $filters['entity'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = :action';
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['from'])) {
            $where[] = 'created_at >= :from';
            $params['from'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $where[] = 'created_at <= :to';
            $params['to'] = $filters['to'];
        }

        $sql = "
            SELECT *
            FROM audit_logs
        ";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
