<?php
declare(strict_types=1);

namespace App\Models;

class SettingsModel extends BaseModel
{
    protected string $table = 'settings';

    public function getWorkOrderSettings(): array
    {
        $row = $this->firstWhere('key', 'work_order_numbering');

        return $row
            ? json_decode($row['value'], true)
            : [
                'format' => '{PREFIX}-{NUMBER}',
                'prefix' => 'WO',
                'number_length' => 5,
                'next_number' => 1,
            ];
    }

    public function updateWorkOrderSettings(array $data): void
    {
        $this->updateWhere('key', 'work_order_numbering', [
            'value' => json_encode($data),
        ]);
    }
}