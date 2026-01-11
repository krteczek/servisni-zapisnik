<?php
declare(strict_types=1);

/**

PDO je globálně nastaveno na FETCH_ASSOC.
V modelech se nikdy fetch mód nespecifikuje.

**/

namespace App\Models;

use App\Core\Database;
use PDO;

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT id, email, employee_number, first_name, last_name, global_role, active, created_at
             FROM users ORDER BY last_name, first_name'
        )->fetchAll();
    }

    public function findByIdFull(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, email, employee_number, first_name, last_name, global_role, active, created_at
             FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }
public function findById(int $id): ?array
{
    $stmt = $this->db->prepare(
        'SELECT id, email, first_name, last_name, global_role, active 
         FROM users 
         WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);

    return $stmt->fetch() ?: null;
}

    public function existsByEmail(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function existsByEmployeeNumber(string $number, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE employee_number = :num';
        $params = ['num' => $number];

        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, employee_number, password_hash, first_name, last_name, global_role, active)
             VALUES (:email, :employee_number, :password_hash, :first_name, :last_name, :global_role, 1)'
        );

        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
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

        $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = :id';
        $this->db->prepare($sql)->execute($params);
    }
    
public function updatePassword(int $userId, string $passwordHash): void
{
    $stmt = $this->db->prepare(
        'UPDATE users
         SET password_hash = :hash
         WHERE id = :id'
    );

    $stmt->execute([
        'id'   => $userId,
        'hash' => $passwordHash,
    ]);
}
   
   
public function availableForTeam(int $teamId): array
{
    $stmt = $this->db->prepare("
        SELECT u.*
        FROM users u
        WHERE u.active = 1
          AND u.id NOT IN (
              SELECT user_id
              FROM team_memberships
              WHERE team_id = ?
                AND valid_to IS NULL
          )
    ");
    $stmt->execute([$teamId]);
    return $stmt->fetchAll();
}
public function findByEmail($email)
{
    $stmt = $this->db->prepare(
        'SELECT id, email, first_name, last_name, global_role, active, password_hash 
         FROM users 
         WHERE email = :email LIMIT 1'
    );
    $stmt->execute(['email' => $email]);

    return $stmt->fetch() ?: null;
	
}


}
