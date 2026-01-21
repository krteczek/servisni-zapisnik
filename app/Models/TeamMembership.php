<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class TeamMembership extends BaseModel
{
    /** název tabulky BEZ prefixu */
    protected string $table = 'team_memberships';

    /* ==========================================================
     * AKTUÁLNÍ ČLENOVÉ TÝMU
     * ========================================================== */

    public function currentMembers(int $teamId): array
    {
        $users = Database::table('users');

        $stmt = $this->db->prepare(
            "SELECT
                tm.id AS membership_id,
                u.id,
                u.first_name,
                u.last_name,
                tm.role_in_team
             FROM {$this->tableName} tm
             JOIN {$users} u ON u.id = tm.user_id
             WHERE tm.team_id = :team
               AND tm.valid_to IS NULL
             ORDER BY u.last_name, u.first_name"
        );

        $stmt->execute(['team' => $teamId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ==========================================================
     * PŘIDÁNÍ UŽIVATELE DO TÝMU
     * - tiché ignorování duplicit
     * ========================================================== */

    public function add(int $userId, int $teamId, string $role): ?int
    {
        // ochrana proti duplicitnímu aktivnímu členství
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM {$this->tableName}
             WHERE user_id = :user
               AND team_id = :team
               AND valid_to IS NULL
             LIMIT 1"
        );

        $stmt->execute([
            'user' => $userId,
            'team' => $teamId,
        ]);

        if ($stmt->fetchColumn()) {
            return null; // už je členem – nic neděláme
        }

        return $this->insert([
            'user_id'      => $userId,
            'team_id'      => $teamId,
            'role_in_team' => $role,
            'valid_from'   => date('Y-m-d'),
        ]);
    }

    /* ==========================================================
     * UKONČENÍ ČLENSTVÍ (SOFT REMOVE)
     * ========================================================== */

    public function end(int $membershipId): void
    {
        $this->updateRow($membershipId, [
            'valid_to' => date('Y-m-d'),
        ]);
    }

    /* ==========================================================
     * AKTIVNÍ TÝMY PODLE UŽIVATELŮ
     * ========================================================== */

    public function activeTeamsByUsers(): array
    {
        $teams = Database::table('teams');

        $stmt = $this->db->query(
            "SELECT
                tm.user_id,
                t.id   AS team_id,
                t.name,
                t.color
             FROM {$this->tableName} tm
             JOIN {$teams} t ON t.id = tm.team_id
             WHERE tm.valid_to IS NULL"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];

        foreach ($rows as $row) {
            $uid = (int) $row['user_id'];

            $out[$uid][] = [
                'id'    => (int) $row['team_id'],
                'name'  => $row['name'],
                'color' => $row['color'],
            ];
        }

        return $out;
    }

    /* ==========================================================
     * ZMĚNA ROLE V TÝMU
     * ========================================================== */

    public function changeRole(int $membershipId, string $role): void
    {
        $this->updateRow($membershipId, [
            'role_in_team' => $role,
        ]);
    }
}
