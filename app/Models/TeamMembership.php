<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class TeamMembership extends BaseModel
{
    protected string $table = 'team_memberships';

    public function currentMembers(int $teamId): array
    {
        $users = Database::table('users');

        $stmt = $this->db->prepare(
            "SELECT tm.id AS membership_id,
							u.id,
                    u.first_name,
                    u.last_name,
                    tm.role_in_team
             FROM {$this->table} tm
             JOIN {$users} u ON u.id = tm.user_id
             WHERE tm.team_id = :team
               AND tm.valid_to IS NULL
             ORDER BY u.last_name"
        );
        $stmt->execute(['team' => $teamId]);

        return $stmt->fetchAll();
    }

    public function add(int $userId, int $teamId, string $role): int
    {
        return $this->insert([
            'user_id'      => $userId,
            'team_id'      => $teamId,
            'role_in_team' => $role,
            'valid_from'   => date('Y-m-d'),
        ]);
    }

    public function end(int $membershipId): void
    {
        $this->updateRow($membershipId, [
            'valid_to' => date('Y-m-d'),
        ]);
    }
    
public function activeTeamsByUsers(): array
{
    $users = Database::table('users');
    $teams = Database::table('teams');

    $stmt = $this->db->query(
        "SELECT
            tm.user_id,
            t.id   AS team_id,
            t.name,
            t.color
         FROM {$this->table} tm
         JOIN {$teams} t ON t.id = tm.team_id
         WHERE tm.valid_to IS NULL"
    );

    $rows = $stmt->fetchAll();

    $out = [];

    foreach ($rows as $row) {
        $uid = (int)$row['user_id'];
        $out[$uid][] = [
            'id'    => (int)$row['team_id'],
            'name'  => $row['name'],
            'color' => $row['color'],
        ];
    }

    return $out;
}
	public function change_user_role(int $id, array $role)
	{
		$this->updateRow($id, $role);
	}

}
