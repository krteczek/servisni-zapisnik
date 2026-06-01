<?php
declare(strict_types=1);

namespace App\Services\Invoice;
use App\Models\WorkOrderModel;
use App\Models\ContactsModel;
use App\Models\TaskModel;
use App\Services\Settings\SettingsService;

final class InvoiceService
{
 
    public function buildDraftFromTask(array $task): array
    {
        $taskStats = (new TaskModel())->statsForTasks([$task['id']]);

        $workOrder = (new WorkOrderModel())
            ->find($task['work_order_id']);

        $customer = (new ContactsModel())
            ->find($workOrder['contact_id'] ?? 0);

        $dueDays = (new SettingsService())->getInvoiceDueDays();
        $customerData = [
            'company_name' => '',
            'ico'          => '',
            'dic'          => '',
            'street'       => '',
            'city'         => '',
            'zip'          => '',
            'country'      => '',
            'email'        => '',
            'phone'        => '',
        ];

        if ($customer) {
            $customerData = array_merge(
                $customerData,
                $customer
            );
        }

        $data =  [

            'invoice' => [
                'title'     => $workOrder['title'],
                'issued_at' => date('Y-m-d'),
                'due_date'  => date('Y-m-d', strtotime('+' . $dueDays . ' days')),
                'note'      => '',
            ],

            'customer' => $customerData,

            'workOrder' => [
                'id'            => $workOrder['id'],
                'title'         => $workOrder['title'],
                'description'   => $workOrder['description'] ?? '',
            ],

            'items' => [
                [
                    'task_id' => $task['id'],
                    'title'   => $task['title'],

                    'minutes' => (int) (
                        $taskStats[$task['id']]['total_minutes']
                        ?? 0
                    ),

                    'kilometers' => (float) (
                        $taskStats[$task['id']]['total_km']
                        ?? 0
                    ),

                    'visible_title' => true,
                    'visible_time'  => true,
                    'visible_km'    => true,
                ],
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