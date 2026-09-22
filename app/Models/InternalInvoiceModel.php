<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;
use App\Core\Auth;
use App\Core\Roles;

class InternalInvoiceModel extends BaseModel
{   
    protected bool $tenantAware = true;
    protected string $table = 'internal_invoices';
    /**
     * Připojení k admin databázi (centrální registr tenantů).
     *
     * @var string
     */
    protected string $connection = 'admin';


/**
 * Vrátí počet záznamů s tenant izolací.
 *
 * @return int
 */
public function count(): int
{
    $where = $this->applyTenant([]);

    $sql = "SELECT COUNT(*) FROM {$this->tableName}";

    if ($where !== []) {
        $parts = [];

        foreach ($where as $col => $val) {
            $parts[] = "{$col} = :{$col}";
        }

        $sql .= ' WHERE ' . implode(' AND ', $parts);
    }

    $stmt = $this->db()->prepare($sql);
    $stmt->execute($where);

    return (int) $stmt->fetchColumn();
}
}