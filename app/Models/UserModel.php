<?php
declare(strict_types=1);

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

    /* =========================
       NAČÍTÁNÍ
       ========================= */

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                email,
                employee_number,
                first_name,
                last_name,
                global_role,
                active,
                created_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM users WHERE email = :email LIMIT 1'
        );

        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                email,
                employee_number,
                first_name,
                last_name,
                global_role,
                active,
                created_at
             FROM users
             ORDER BY last_name, first_name'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       VYTVÁŘENÍ
       ========================= */

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (
                email,
                employee_number,
                password_hash,
                first_name,
                last_name,
                global_role,
                active
            ) VALUES (
                :email,
                :employee_number,
                :password_hash,
                :first_name,
                :last_name,
                :global_role,
                1
            )'
        );

        $stmt->execute([
            'email'         => $data['email'],
            'employee_number' => $data['employee_number'],
            'password_hash' => $data['password_hash'],
            'first_name'    => $data['first_name'] ?? null,
            'last_name'     => $data['last_name'] ?? null,
            'global_role'   => $data['global_role'] ?? 'monter',
            'active'        => 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /* =========================
       AKTIVACE / DEAKTIVACE
       ========================= */

    public function deactivate(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET active = 0 WHERE id = :id'
        );

        $stmt->execute(['id' => $userId]);
    }

    public function activate(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET active = 1 WHERE id = :id'
        );

        $stmt->execute(['id' => $userId]);
    }

    /* =========================
       ROLE
       ========================= */

    public function setGlobalRole(int $userId, string $role): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET global_role = :role
             WHERE id = :id'
        );

        $stmt->execute([
            'id'   => $userId,
            'global_role' => $role,
        ]);
    }

    /* =========================
       HESLO
       ========================= */

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
    
public function existsByEmployeeNumber(string $number): bool
{
    $stmt = $this->db->prepare(
        'SELECT 1 FROM users WHERE employee_number = :num LIMIT 1'
    );
    $stmt->execute(['num' => $number]);
    return (bool) $stmt->fetchColumn();
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

    $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = :id';

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
}

public function findByIdFull(int $id): ?array
{
    $stmt = $this->db->prepare(
        'SELECT
            id,
            email,
            employee_number,
            first_name,
            last_name,
            global_role,
            active,
            created_at
         FROM users
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}



}
