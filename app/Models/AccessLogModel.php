<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class AccessLogModel extends BaseModel
{
    protected string $table = 'access_logs';

    public function log(array $data): void
    {
        parent::insert([
            'user_id'    => $data['user_id'],
            'ip_address' => $data['ip_address'],
            'type'       => $data['type'],
            'path'       => $data['path'],
            'method'     => $data['method'],
            'user_agent' => $data['user_agent'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function countRecent(
        string $type,
        string $ip,
        int $minutes
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM {$this->tableName}
            WHERE type = :type
              AND ip_address = :ip
              AND created_at >= NOW() - INTERVAL :min MINUTE
        ");

        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':min', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
