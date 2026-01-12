<?php
declare(strict_types=1);

namespace App\Models;

class UserModel extends BaseModel
{
    public function all(): array
    {
        return self::db()->query(
            'SELECT id, email, employee_number, first_name, last_name, global_role, active, created_at
             FROM ' . self::table('users') . '
             ORDER BY last_name, first_name'
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT id, email, first_name, last_name, global_role, active
             FROM ' . self::table('users') . '
             WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByIdFull(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT id, email, employee_number, first_name, last_name, global_role, active, created_at
             FROM ' . self::table('users') . '
             WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT id, email, first_name, last_name, global_role, active, password_hash
             FROM ' . self::table('users') . '
             WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO ' . self::table('users') . '
            (email, employee_number, password_hash, first_name, last_name, global_role, active)
            VALUES (:email, :employee_number, :password_hash, :first_name, :last_name, :global_role, 1)'
        );

        $stmt->execute($data);

        return (int) self::db()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $allowed = ['email', 'employee_number', 'first_name', 'last_name', 'global_role', 'active'];
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

        $sql = 'UPDATE ' . self::table('users') .
               ' SET ' . implode(', ', $set) .
               ' WHERE id = :id';

        self::db()->prepare($sql)->execute($params);
    }

    public function updatePassword(int $userId, string $hash): void
    {
        self::db()->prepare(
            'UPDATE ' . self::table('users') . '
             SET password_hash = :hash
             WHERE id = :id'
        )->execute([
            'id' => $userId,
            'hash' => $hash,
        ]);
    }

    public function availableForTeam(int $teamId): array
    {
        $stmt = self::db()->prepare("
            SELECT u.*
            FROM " . self::table('users') . " u
            WHERE u.active = 1
              AND u.id NOT IN (
                  SELECT user_id
                  FROM " . self::table('team_memberships') . "
                  WHERE team_id = ?
                    AND valid_to IS NULL
              )
        ");
        $stmt->execute([$teamId]);

        return $stmt->fetchAll();
    }
}