<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
class Team
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    public function allWithMembersCount(): array
    {
        return $this->db->query("
            SELECT t.*, COUNT(tm.id) AS members
            FROM teams t
            LEFT JOIN team_memberships tm 
              ON tm.team_id = t.id AND tm.valid_to IS NULL
            GROUP BY t.id
        ")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $color): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO teams (name, color) VALUES (?, ?)
        ");
        $stmt->execute([$name, $color]);
    }
}
