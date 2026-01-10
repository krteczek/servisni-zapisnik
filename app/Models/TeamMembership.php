<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class TeamMembership
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    public function currentMembers(int $teamId): array
    {
        $stmt = $this->db->prepare("
            SELECT tm.*, u.first_name, u.last_name
            FROM team_memberships tm
            JOIN users u ON u.id = tm.user_id
            WHERE tm.team_id = ?
              AND tm.valid_to IS NULL
        ");
        $stmt->execute([$teamId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $teamId, string $role): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO team_memberships 
            (user_id, team_id, role_in_team, valid_from)
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt->execute([$userId, $teamId, $role]);
    }

    public function end(int $membershipId): void
    {
        $stmt = $this->db->prepare("
            UPDATE team_memberships
            SET valid_to = CURDATE()
            WHERE id = ?
        ");
        $stmt->execute([$membershipId]);
    }
}
