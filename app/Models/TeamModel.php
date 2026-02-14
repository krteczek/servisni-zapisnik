<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class TeamModel extends BaseModel
{
    protected string $table = 'teams';

    /**
     * Vrátí všechny týmy (tenant-aware řeší BaseModel)
     */
    public function all(): array
    {
        return parent::all();
    }

    /**
     * Najde tým podle ID (tenant-aware řeší BaseModel)
     */
    public function find(int $id): ?array
    {
        return parent::find($id);
    }

    /**
     * Aktivace / deaktivace týmu
     */
    public function setActive(int $teamId, bool $active): bool
    {
        return $this->update($teamId, [
            'active' => $active ? 1 : 0
        ]);
    }

    /**
     * Vrátí týmy podle aktivního stavu
     */
    public function byActive(bool $active): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE active = :active
            {$this->tenantWhere()}
            ORDER BY name
        ";

        return $this->fetchAll($sql, [
            'active' => $active ? 1 : 0,
            'company_id' => parent::tenantId()
        ]);
    }

    /**
     * Vrátí barvy týmů podle ID (tenant-aware!)
     */
public function getColorsByIds(array $teamIds): array
{
    $teamIds = array_values(array_unique(
        array_filter(array_map('intval', $teamIds))
    ));

    if ($teamIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

    $sql = "
        SELECT id, color
        FROM {$this->tableName}
        WHERE id IN ($placeholders)
    ";

    $params = $teamIds;

    if ($this->tenantAware) {
        $sql .= " AND {$this->tenantColumn} = ?";
        $params[] = parent::tenantId();
    }

    $stmt = $this->db()->prepare($sql);
    $stmt->execute($params);

    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $out[(int)$row['id']] = $row['color'];
    }

    return $out;
}
}
