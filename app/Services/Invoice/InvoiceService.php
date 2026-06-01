<?php
declare(strict_types=1);

namespace App\Services\Invoice;
use App\Models\WorkOrderModel;
use App\Models\ContactsModel;
use App\Models\TaskModel;

final class InvoiceService
{
    /**
     * Vytvoří fakturu z jednoho úkolu.
     */
    public function createFromTask(
        array $data
    ): int {
    }

    public function buildDraftFromTask(array $task,): array
    {
        $data = [];
        $taskStats = (new TaskModel())->statsForTasks([$task['id']]);
        $workOrder = (new WorkOrderModel())->find($task['work_order_id']);
        $customer  = (new ContactsModel())->find($workOrder['contact_id'] ?? 0);
        return [
            'task'      => array_merge($task, ['stats' => $taskStats[$task['id']] ?? []]),
            'workOrder' => $workOrder,
            'customer'  => $customer,

            'invoice' => [
                'issued_at' => date('Y-m-d'),
                'due_date'  => date('Y-m-d', strtotime('+14 days')),
            ],
    ];


        return $data;
    }

    /**
     * Vytvoří fakturu z celé zakázky.
     */
    public function createFromWorkOrder(
        int $companyId,
        int $workOrderId,
        array $data
    ): int {
    }

    /**
     * Vytvoří fakturu z billing exportu.
     */
    public function createFromExport(
        int $companyId,
        int $exportId,
        array $data
    ): int {
    }

    /**
     * Detail faktury.
     */
    public function getDetail(
        int $companyId,
        int $invoiceId
    ): ?array {
    }

    /**
     * Seznam faktur.
     */
    public function getInvoices(
        int $companyId
    ): array {
    }

    /**
     * Storno.
     */
    public function cancel(
        int $companyId,
        int $invoiceId,
        int $userId,
        ?string $reason = null
    ): void {
    }

    /**
     * PDF.
     */
    public function generatePdf(
        int $companyId,
        int $invoiceId
    ): string {
    }

    private function createInvoice(array $invoice): int
    {
    }

    private function getTaskSnapshot(
        int $companyId,
        int $taskId
    ): array {
    }

    private function getWorkOrderSnapshot(
        int $companyId,
        int $workOrderId
    ): array {
    }

    private function getExportSnapshot(
        int $companyId,
        int $exportId
    ): array {
    }

    private function renderPdf(
        array $invoice
    ): string {
    }
}