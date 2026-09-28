<?php
declare(strict_types=1);

namespace App\Services\Invoice;

use App\Core\Url;
use App\Core\Flash;
use App\Models\InternalInvoiceItemModel;
use App\Models\TaskModel;
use App\Services\Tasks\TaskStatus;
use App\Services\Tasks\TaskType;

final class InvoiceGuard
{
    /**
     * Ověří, zda lze úkol fakturovat.
     *
     * @param int $taskId ID úkolu
     * @return array<string, mixed> Ověřený úkol
     */
    public function assertTaskCanBeInvoiced(int $taskId): array
    {
        if ($taskId <= 0) {
            Flash::error('Požadovaný úkol neexistuje.');
            Url::back();
           
        }

        $task = (new TaskModel())->findById($taskId);

        if ($task === null) {
            Flash::error('Požadovaný úkol neexistuje.');
            Url::back();
        }

        if ($task['status'] !== TaskStatus::DONE) {
            Flash::error('Fakturovat lze pouze dokončený úkol.');
            Url::back();
        }

        if ($task['task_type'] === TaskType::RECURRING_MASTER) {
            Flash::error('Šablonu opakovaného úkolu nelze fakturovat.');
            Url::back();
       }

        $invoiceItems = new InternalInvoiceItemModel();

        if ($invoiceItems->isTaskActivelyInvoiced($taskId)) {
            Flash::error('Tento úkol již byl fakturován.');
            Url::back();
         }

        return $task; 
    }
}