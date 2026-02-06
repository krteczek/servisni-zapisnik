<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Auth;

class Team extends BaseModel
{
    protected string $table = 'teams';

    public function all(): array
    {
        return $this->select('*', [], 'id');
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT *
             FROM {$this->tableName}
             WHERE id = :id
               AND company_id = :company_id
             LIMIT 1"
        );

        $stmt->execute([
            'id'         => $id,
            'company_id' => Auth::companyId(),
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(string $name, string $color): int
    {
        return $this->insert([
            'name'       => $name,
            'color'      => $color,
            'company_id' => Auth::companyId(),
        ]);
    }

    public function update(int $id, string $name, string $color): bool
    {
        return $this->updateWhere(
            [
                'id'         => $id,
                'company_id' => Auth::companyId(),
            ],
            [
                'name'  => $name,
                'color' => $color,
            ]
        );
    }

    public function updateTeam(int $id, array $data): bool
    {
        return $this->updateWhere(
            [
                'id'         => $id,
                'company_id' => Auth::companyId(),
            ],
            $data
        );
    }

    public function setActive(int $teamId, bool $active): bool
    {
        return $this->updateWhere(
            [
                'id'         => $teamId,
                'company_id' => Auth::companyId(),
            ],
            [
                'active' => $active ? 1 : 0
            ]
        );
    }

    public function byActive(bool $active): array
    {
        $stmt = $this->db()->prepare(
            "SELECT *
             FROM {$this->tableName}
             WHERE active = :active
               AND company_id = :company_id
             ORDER BY name"
        );

        $stmt->execute([
            'active'     => $active ? 1 : 0,
            'company_id' => Auth::companyId(),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
