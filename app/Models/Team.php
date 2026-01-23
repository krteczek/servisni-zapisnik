<?php
declare(strict_types=1);

namespace App\Models;
use \PDO;
class Team extends BaseModel
{
    protected string $table = 'teams';

    public function all(): array
    {
        return $this->allRows('id');
    }

    public function find(int $id): ?array
    {
        return $this->findRow($id);
    }

    public function create(string $name, string $color): int
    {
        return $this->insert([
            'name'  => $name,
            'color'=> $color,
        ]);
    }

    public function update(int $id, string $name, string $color): void
    {
        $this->updateRow($id, [
            'name'  => $name,
            'color'=> $color,
        ]);
    }
    public function updateTeam(int $id, array $data): void
{
    $this->updateRow($id, $data);
}

public function setActive(int $teamId, bool $active): void
{
    $team = $this->find($teamId);

    if (!$team) {
        throw new \RuntimeException('Tým nenalezen');
    }

    $newData = ['active' => $active ? 1 : 0];

    $this->updateRow($teamId, $newData);
  
}

public function byActive(bool $active): array
{
    $stmt = $this->db->prepare(
        "SELECT *
         FROM {$this->tableName}
         WHERE active = :active
         ORDER BY name"
    );

    $stmt->execute([
        'active' => $active ? 1 : 0
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
