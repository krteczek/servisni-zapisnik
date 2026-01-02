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
       ZÁKLADNÍ NAČÍTÁNÍ
       ========================= */

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT 
            	email,
            	first_name,
            	last_name,
            	global_role,
            	active
             FROM users
             WHERE id = :id
               AND active = 1
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM users
             WHERE email = :email
               AND active = 1
             LIMIT 1'
        );

        $stmt->execute([
            'email' => $email,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'email' => $email,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /* =========================
       VYTVÁŘENÍ UŽIVATELE
       ========================= */

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (
                email,
                password_hash,
                global_role,
                first_name,
                last_name,
                employee_number,
                birth_date,
                employment_start,
                employment_end,
                role_id,
                hired_at,
                terminated_at,
                active
            ) VALUES (
                :email,
                :password_hash,
                :global_role,
                :first_name,
                :last_name,
                :employee_number,
                :birth_date,
                :employment_start,
                :employment_end,
                :role_id,
                :hired_at,
                :terminated_at,
                :active
            )'
        );

        $stmt->execute([
            'email'             => $data['email'],
            'password_hash'     => $data['password_hash'],
            'global_role'       => $data['global_role'] ?? 'user',

            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'employee_number'   => $data['employee_number'] ?? null,

            'birth_date'        => $data['birth_date'] ?? null,

            'employment_start'  => $data['employment_start'],
            'employment_end'    => $data['employment_end'] ?? null,

            'role_id'           => $data['role_id'],
            'hired_at'          => $data['hired_at'],
            'terminated_at'     => $data['terminated_at'] ?? null,

            'active'            => 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /* =========================
       ROLE
       ========================= */

    public function getGlobalRole(int $userId): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT global_role
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $userId,
        ]);

        $role = $stmt->fetchColumn();

        return $role !== false ? (string) $role : null;
    }
    
    public function getPermissions(int $roleId): array
{
    $stmt = $this->db->prepare(
        'SELECT p.code
         FROM permissions p
         JOIN role_permissions rp ON rp.permission_id = p.id
         WHERE rp.role_id = ?'
    );
    $stmt->execute([$roleId]);

    return array_column($stmt->fetchAll(), 'code');
}

}
