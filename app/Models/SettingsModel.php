<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\Settings\BillingMode;
//use App\Services\Settings\BillingMode;
//use \LogicException;


class SettingsModel extends BaseModel
{
    protected string $table = 'settings';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;

    private array $cache = [];

    private array $defaults = [
        'work_order_numbering' => [
            'format' => '{PREFIX}-{NUMBER}',
            'prefix' => 'WO',
            'number_length' => 5,
            'next_number' => 1,
        ],

        'billing' => [
            'billing_mode' => BillingMode::INTERNAL, //'internal', //external
        ],
        
    ];

    public function getWorkOrderSettings(): array
    {
        return $this->get('work_order_numbering');
    }

    public function updateWorkOrderSettings(array $data): void
    {
        $this->set('work_order_numbering', $data);
    }

    public function get(string $key, $default = null): array
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
			if (!array_key_exists($key, $this->defaults)) {
			    throw new \LogicException(
			        "SettingsModel.get: unknown key '{$key}'"
			    );
			}

        $default = $default ?? $this->defaults[$key];

        $row = $this->firstWhere('setting_key', $key);

        if ($row === null) {
            return $this->cache[$key] = $default;
        }

        $data = json_decode($row['value'], true);

        return $this->cache[$key] = is_array($data) ? $data : $default;
    }

    public function set(string $key, array $value): void
    {
         $sql = "INSERT INTO {$this->tableName}
                    (`setting_key`, `value`, {$this->tenantColumn})
                VALUES (:key, :value_insert, :tenant)
                ON DUPLICATE KEY UPDATE `value` = :value_update";

        $valueJson = json_encode($value, JSON_THROW_ON_ERROR);
        $this->db()->prepare($sql)->execute([
            'key'          => $key,
            'value_insert' => $valueJson,
            'value_update' => $valueJson,
            'tenant'       => $this->tenantId(),
        ]);

        unset($this->cache[$key]);
    }

    public function getBillingSettings(): array
    {
        return $this->get('billing');
    }

    public function updateBillingSettings(array $data): void
    {
        $this->set('billing', $data);
    }
}