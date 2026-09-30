<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model fakturačních řádků.
 *
 * Řádek obsahuje množství, jednotku množství,
 * jednotku sazby, sazbu, měnu a výslednou částku.
 *
 * Vazba řádku na úkol není uložena v této tabulce.
 * Pokud řádek souvisí s úkolem, je tato informace
 * součástí snapshotu faktury.
 */
class InternalInvoiceLineModel extends BaseModel
{
    protected bool $tenantAware = true;
    protected string $table = 'internal_invoice_lines';
    protected string $connection = 'admin';

    /**
     * Vrátí všechny řádky konkrétní faktury.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forInvoice(int $invoiceId): array
    {
        if ($invoiceId <= 0) {
            return [];
        }

        $where = $this->applyTenant([
            'invoice_id' => $invoiceId,
        ]);

        $parts = [];

        foreach ($where as $column => $value) {
            $parts[] = "{$column} = :{$column}";
        }

        $sql = "
            SELECT
                id,
                company_id,
                invoice_id,
                description,
                quantity,
                unit,
                price_unit,
                unit_price,
                total,
                currency,
                created_at
            FROM {$this->tableName}
            WHERE " . implode(' AND ', $parts) . "
            ORDER BY id ASC
        ";

        return $this->fetchAll($sql, $where);
    }

    /**
     * Vytvoří nový fakturační řádek.
     *
     * `total` musí být před výpočtem připravený serverem.
     *
     * @param array{
     *     invoice_id:int,
     *     description:string,
     *     quantity:float,
     *     unit:string,
     *     price_unit:string,
     *     unit_price:float,
     *     total:float,
     *     currency:string
     * } $data
     */
    public function createLine(array $data): int
    {
        return $this->create($data);
    }
}