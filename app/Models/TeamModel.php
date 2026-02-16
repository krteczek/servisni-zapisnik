<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class TeamModel extends BaseModel
{
    protected string $table = 'teams';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;


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

public function getColorsAndNamesByIdsOld(array $ids): array
{
    if (empty($ids)) {
        return [];
    }

    $params = [];
    $placeholders = [];

    foreach ($ids as $i => $id) {
        $key = "id_$i";
        $placeholders[] = ":$key";
        $params[$key] = (int) $id;
    }

    $sql = "
        SELECT id, name, color
        FROM {$this->tableName}
        WHERE id IN (" . implode(',', $placeholders) . ")
    ";

    $rows = $this->fetchAll($sql, $params);

    $result = [];

    foreach ($rows as $row) {
        $result[(int) $row['id']] = [
            'name'  => $row['name'],
            'color' => $row['color'],
        ];
    }

    return $result;
}

    
    /**
     * Vrátí aktivní členy týmu (kteří mají platné členství)
     */
    public function getActiveMembers(int $teamId): array
    {
        // Použijeme existující TeamMembership model
        $membershipModel = new TeamMembership();
        
        // Jejich metoda currentMembers() už vrací aktivní členy
        // (používá valid_to IS NULL)
        return $membershipModel->currentMembers($teamId);
    }

    /**
     * Vrátí názvy a barvy týmů podle ID
     */
    public function getColorsAndNamesByIds(array $teamIds): array
    {
        if (empty($teamIds)) {
            return [];
        }
        
        $placeholders = [];
        $params = ['company_id' => $this->tenantId()];
        
        foreach ($teamIds as $i => $id) {
            $key = "id_$i";
            $placeholders[] = ":$key";
            $params[$key] = (int) $id;
        }
        
        $sql = "
            SELECT id, name, color
            FROM {$this->tableName}
            WHERE id IN (" . implode(',', $placeholders) . ")
                AND company_id = :company_id
        ";
        
        $teams = $this->fetchAll($sql, $params);
        
        $result = [];
        foreach ($teams as $team) {
            $result[(int)$team['id']] = [
                'name' => $team['name'],
                'color' => $team['color']
            ];
        }
        
        return $result;
    }
    
    /**
     * Vrátí všechny aktivní týmy firmy
     */
    public function getAllActive(): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE company_id = :company_id
                AND active = 1
            ORDER BY name
        ";
        
        return $this->fetchAll($sql, [
            'company_id' => $this->tenantId()
        ]);
    }
}

