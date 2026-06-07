<?php
declare(strict_types=1);

namespace App\Services\Invoice;
use App\Models\WorkOrderModel;
use App\Models\ContactsModel;
use App\Models\TaskModel;
use App\Services\Settings\SettingsService;
use App\Core\Csrf;

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

public function invoiceFromTask(
    int $taskId,
    array $post
): array
{
    $errors = [];

    $task = (new TaskModel())->findById($taskId);

    if (!$task) {
        throw new RuntimeException('Task not found');
    }

    $workOrder = (new WorkOrderModel())->find(
        (int) $task['work_order_id']
    );

    if (!$workOrder) {
        throw new RuntimeException('Work order not found');
    }

    // =====================
    // VALIDACE
    // =====================

    $title = trim((string) ($post['title'] ?? ''));

    if ($title === '') {
        $errors['title'] =
            'Název faktury je povinný.';
    }

    $issuedAt = (string) ($post['issued_at'] ?? '');

    if ($issuedAt === '') {
        $errors['issued_at'] =
            'Datum vystavení je povinné.';
    }

    $dueDate = (string) ($post['due_date'] ?? '');

    if ($dueDate === '') {
        $errors['due_date'] =
            'Datum splatnosti je povinné.';
    }

    if (
        $issuedAt !== ''
        && $dueDate !== ''
        && strtotime($dueDate) < strtotime($issuedAt)
    ) {
        $errors['due_date'] =
            'Datum splatnosti musí být pozdější než datum vystavení.';
    }

    // =====================
    // PŘI CHYBĚ
    // =====================

    if ($errors) {

        $draft = $this->buildDraftFromTask($task);

        $draft['invoice']['title'] =
            $title;

        $draft['invoice']['issued_at'] =
            $issuedAt;

        $draft['invoice']['due_date'] =
            $dueDate;

        $draft['invoice']['note'] =
            (string) ($post['note'] ?? '');

        $draft['customer'] =
            $post['customer'] ?? [];

        $draft['items'] =
            $post['items'] ?? [];

        return [
            'success' => false,
            'errors'  => $errors,
            'data'    => $draft,
        ];
    }

    // =====================
    // ULOŽENÍ
    // =====================

    $invoiceId = $this->createInvoice(
        [$task],
        $workOrder,
        $post
    );

    return [
        'success'    => true,
        'invoice_id' => $invoiceId,
    ];
}
    private function validateInvoiceData(array $data): array
    {
        
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