<?php
declare(strict_types=1);

namespace App\Models;

class TeamMembership extends BaseModel
{
    public static function currentMembers(int $teamId): array
    {
        $stmt = self::db()->prepare("
            SELECT tm.id AS membership_id,
                   u.first_name,
                   u.last_name,
                   tm.role_in_team
            FROM " . self::table('team_memberships') . " tm
            JOIN " . self::table('users') . " u ON u.id = tm.user_id
            WHERE tm.team_id = ?
              AND tm.valid_to IS NULL
            ORDER BY u.last_name
        ");
        $stmt->execute([$teamId]);

        return $stmt->fetchAll();
    }

    public static function add(int $userId, int $teamId, string $role): void
    {
        $stmt = self::db()->prepare("
            INSERT INTO " . self::table('team_memberships') . "
            (user_id, team_id, role_in_team, valid_from)
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt->execute([$userId, $teamId, $role]);
    }

    public static function end(int $membershipId): void
    {
        $stmt = self::db()->prepare("
            UPDATE " . self::table('team_memberships') . "
            SET valid_to = CURDATE()
            WHERE id = ?
        ");
        $stmt->execute([$membershipId]);
    }
}