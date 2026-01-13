<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class UserModel extends BaseModel
{
    /** název tabulky bez prefixu */
    protected string $table = 'users';

    public function all(): array
    {
        return $this->db->query(
            "SELECT id, email, employee_number, first_name, last_name, global_role, active, created_at
             FROM {$this->table}
             ORDER BY last_name, first_name"
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, email, first_name, last_name, global_role, active
             FROM {$this->table}
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByIdFull(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, email, employee_number, first_name, last_name, global_role, active, created_at
             FROM {$this->table}
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, email, first_name, last_name, global_role, active, password_hash
             FROM {$this->table}
             WHERE email = :email
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (email, employee_number, password_hash, first_name, last_name, global_role, active)
             VALUES (:email, :employee_number, :password_hash, :first_name, :last_name, :global_role, 1)"
        );

        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $allowed = [
            'email',
            'employee_number',
            'first_name',
            'last_name',
            'global_role',
            'active',
        ];

        $set = [];
        $params = ['id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $set[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (!$set) {
            return;
        }

        $sql = "UPDATE {$this->table}
                SET " . implode(', ', $set) . "
                WHERE id = :id";

        $this->db->prepare($sql)->execute($params);
    }

    public function updatePassword(int $userId, string $hash): void
    {
        $this->db->prepare(
            "UPDATE {$this->table}
             SET password_hash = :hash
             WHERE id = :id"
        )->execute([
            'id'   => $userId,
            'hash' => $hash,
        ]);
    }

    public function availableForTeam(int $teamId): array
    {
        $teamMemberships = Database::table('team_memberships');

        $stmt = $this->db->prepare(
            "SELECT u.*
             FROM {$this->table} u
             WHERE u.active = 1
               AND u.id NOT IN (
                   SELECT user_id
                   FROM {$teamMemberships}
                   WHERE team_id = :team
                     AND valid_to IS NULL
               )"
        );
        $stmt->execute(['team' => $teamId]);

        return $stmt->fetchAll();
    }
}
