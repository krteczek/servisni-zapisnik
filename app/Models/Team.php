<?php
declare(strict_types=1);

namespace App\Models;

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
}
