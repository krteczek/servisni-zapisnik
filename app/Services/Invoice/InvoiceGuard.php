<?php
declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\InternalInvoiceItemModel;
use App\Models\TaskModel;
use App\Services\Tasks\TaskStatus;
use App\Services\Tasks\TaskType;
use RuntimeException;

final class InvoiceGuard
{
    /**
     * Ověří, zda lze úkol fakturovat.
     *
     * @param int $taskId ID úkolu
     * @return array<string, mixed> Ověřený úkol
     *
     * @throws RuntimeException Pokud úkol nelze fakturovat.
     */
    public function assertTaskCanBeInvoiced(int $taskId): array
    {
        if ($taskId <= 0) {
            throw new RuntimeException(
                'Požadovaný úkol neexistuje.'
            );
        }

        $task = (new TaskModel())->findById($taskId);

        if ($task === null) {
            throw new RuntimeException(
                'Požadovaný úkol neexistuje.'
            );
        }

        if ($task['status'] !== TaskStatus::DONE) {
            throw new RuntimeException(
                'Fakturovat lze pouze dokončený úkol.'
            );
        }

        if ($task['task_type'] === TaskType::RECURRING_MASTER) {
            throw new RuntimeException(
                'Šablonu opakovaného úkolu nelze fakturovat.'
            );
        }

        $invoiceItems = new InternalInvoiceItemModel();

        if ($invoiceItems->isTaskActivelyInvoiced($taskId)) {
            throw new RuntimeException(
                'Tento úkol již byl fakturován.'
            );
        }

        return $task;
    }
}