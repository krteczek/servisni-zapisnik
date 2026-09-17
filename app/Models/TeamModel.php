<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use LogicException;
use App\Core\Types;

/**
 * @phpstan-import-type TeamRow from Types
 */
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
     * @param bool $active
     * @return array<int, TeamRow>     */
    public function byActive(bool $active): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName}
            WHERE active = :active
            AND {$this->tenantColumn} = :{$this->tenantColumn}
            ORDER BY name
        ";

        return $this->fetchAll($sql, [
            'active' => $active ? 1 : 0,
            $this->tenantColumn => $this->tenantId(),
        ]);
    }

    /**
     * Vrátí barvy týmů podle ID (tenant-aware!)
     * @param array<int, int> $teamIds
     * @return array<int, array<string, mixed>>
     */
        public function getColorsByIds(array $teamIds): array
    {
        $teamIds = array_values(array_unique(
            array_filter(
                array_map('intval', $teamIds),
                static fn (int $id): bool => $id !== 0)
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



    
    /**
     * Vrátí aktivní členy týmu (kteří mají platné členství)
     * @param int $teamId
     * @return array<int, array<string, mixed>>
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
     * 
     * @param array<int, int> $teamIds
     * @return array<int, array<string, mixed>>
     */
    public function getColorsAndNamesByIds(array $teamIds): array
    {
        if ($teamIds === []) {
            return [];
        }
        
        $placeholders = [];
        $params = ['company_id' => $this->tenantId()];
        
        foreach ($teamIds as $i => $id) {
            $key = "id_$i";
            $placeholders[] = ":$key";
            $params[$key] = $id;
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
     * @return array<int, array<string, mixed>>
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

     protected function tenantWhere(string $alias = ''): string
    {
        throw new LogicException('Nepoužívej tenantWhere(), použij explicitní podmínku.');
    }


}

