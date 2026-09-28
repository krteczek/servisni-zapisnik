<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;

final class InternalInvoiceItemModel extends BaseModel
{
    protected bool $tenantAware = true;

    protected string $table = 'internal_invoice_items';

    protected string $connection = 'admin';

    /**
     * Zjistí, zda má úkol aktivní fakturu.
     *
     * Draft ani stornovaná faktura se za aktivní nepovažují.
     *
     * @param int $taskId ID úkolu
     * @return bool
     */
    public function isTaskActivelyInvoiced(int $taskId): bool
    {
        if ($taskId <= 0) {
            return false;
        }

        $sql = "
            SELECT 1
            FROM internal_invoice_items iit
            INNER JOIN internal_invoices ii
                ON ii.id = iit.invoice_id
               AND ii.company_id = iit.company_id
            WHERE iit.company_id = :company_id
              AND iit.task_id = :task_id
              AND ii.status IN ('issued', 'paid')
            LIMIT 1
        ";

        return $this->fetchOne($sql, [
            'company_id' => Auth::companyId(),
            'task_id'    => $taskId,
        ]) !== null;
    }
}