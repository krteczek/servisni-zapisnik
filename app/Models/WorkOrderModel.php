<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class WorkOrderModel extends BaseModel
{
    protected string $table = 'work_orders';

    public function create(array $data): int
    {
        return $this->insert([
            'external_number'     => $data['external_number'],
            'title'               => $data['title'],
            'description'         => $data['description'],
            'source'              => $data['source'],
            'requested_by'        => $data['requested_by'],
            'priority'            => $data['priority'],
            'created_by_user_id'  => $data['user_id'],
        ]);
    }

    public function all(): array
    {
        return $this->db
            ->query("SELECT * FROM {$this->table} ORDER BY created_at DESC")
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        return $this->findRow($id);
    }
    
    public function update(int $id, array $data): void
{
    $this->updateRow($id, [
        'external_number' => $data['external_number'],
        'title'           => $data['title'],
        'description'     => $data['description'],
        'source'          => $data['source'],
        'requested_by'    => $data['requested_by'],
        'contact'         => $data['contact'],
        'priority'        => $data['priority'],
    ]);
}
}
