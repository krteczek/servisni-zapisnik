<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Config;
use PDO;
use LogicException;

final class AuditLogModel
{
    protected PDO $db;

    protected string $table = 'audit_logs';
    protected string $connection = 'admin';

    protected string $tableName;

    protected array $orderable = ['id', 'created_at'];

public function __construct()
{
    if ($this->table === '') {
        throw new LogicException('AuditLogModel table name is empty');
    }

    $this->db = match ($this->connection) {
        'admin' => Database::admin(),
        'work'  => Database::work(),
        default => throw new LogicException(
            'Unknown DB connection: ' . $this->connection
        ),
    };

    $prefix = (string) Config::get('database.prefix', '');
    $this->tableName = $prefix . $this->table;
}
    public function insert(array $data): void
    {
        $cols   = array_keys($data);
        $fields = implode(', ', $cols);
        $values = ':' . implode(', :', $cols);

        $sql = "INSERT INTO {$this->tableName} ({$fields}) VALUES ({$values})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByFilters(array $filters, int $limit = 100): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = :action';
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['table'])) {
            $where[] = 'entity = :entity';
            $params['entity'] = $filters['table'];
        }

        if (!empty($filters['ip'])) {
            $where[] = 'ip_address = :ip';
            $params['ip'] = $filters['ip'];
        }

        if (!empty($filters['from'])) {
            $where[] = 'created_at >= :from';
            $params['from'] = $filters['from'] . ' 00:00:00';
        }

        if (!empty($filters['to'])) {
            $where[] = 'created_at <= :to';
            $params['to'] = $filters['to'] . ' 23:59:59';
        }

        $sql = "SELECT * FROM {$this->tableName}";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ' . (int) $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
