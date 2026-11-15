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
     * Vrátí faktury a návrhy pro výpis na indexu.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function forIndex(array $filters = []): array
    {
        $search    = trim((string) ($filters['search'] ?? ''));
        $status    = (string) ($filters['status'] ?? 'all');
        $order     = (string) ($filters['order'] ?? 'created_at');
        $direction = strtoupper((string) ($filters['direction'] ?? 'DESC'));

        $allowedStatuses = [
            'draft',
            'issued',
            'paid',
            'cancelled',
        ];

        $allowedOrders = [
            'created_at'     => 'i.created_at',
            'issued_at'      => 'i.issued_at',
            'due_date'       => 'i.due_date',
            'invoice_number' => 'i.invoice_number',
            'customer_name'  => 'i.customer_name',
            'amount'         => 'total_amount',
        ];

        $orderBy = $allowedOrders[$order] ?? 'i.created_at';

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        $where = $this->applyTenant([]);

        $conditions = [];
        $params = [];

        foreach ($where as $column => $value) {
            $conditions[] = "i.{$column} = :{$column}";
            $params[$column] = $value;
        }

        if ($search !== '') {
            $conditions[] = '(
                i.invoice_number LIKE :search
                OR i.customer_name LIKE :search
            )';

            $params['search'] = '%' . $search . '%';
        }

        if ($status !== 'all' && in_array($status, $allowedStatuses, true)) {
            $conditions[] = 'i.status = :status';
            $params['status'] = $status;
        }

        $lineModel = new InternalInvoiceLineModel();
        $lineTable = $lineModel->getTableName();
        $sql = "
            SELECT
                i.*,
                COALESCE(il.total_amount, 0) AS total_amount
            FROM {$this->tableName} i
            LEFT JOIN (
                SELECT
                    invoice_id,
                    SUM(total) AS total_amount
                FROM {$lineTable}
                WHERE company_id = :lines_company_id
                GROUP BY invoice_id
            ) il ON il.invoice_id = i.id
        ";

        $params['lines_company_id'] = $where['company_id'];

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY {$orderBy} {$direction}, i.id DESC";

        return $this->fetchAll($sql, $params);
    }


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