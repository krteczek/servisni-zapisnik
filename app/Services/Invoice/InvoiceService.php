<?php
declare(strict_types=1);

namespace App\Services\Invoice;
use App\Models\WorkOrderModel;
use App\Models\ContactsModel;
use App\Models\TaskModel;
use App\Services\Settings\SettingsService;
use App\Core\Csrf;
use \RuntimeException;
use \App\Core\Flash;
use \App\Core\Url;
final class InvoiceService
{

    /**
     * @param int $id
     * @return array<string, mixed>
     */
    private function requireTaskForInvoice(int $id): array
    {
        if ($id <= 0) {
            Flash::error('Požadovaný úkol neexistuje');
            Url::back();
        }

        $task = (new TaskModel())->findById($id);

        if ($task === null) {
            Flash::error('Požadovaný úkol neexistuje');
            Url::back();
        }

        if ($task['status'] !== 'done') {
            Flash::error(
                'Fakturovat lze pouze dokončené úkoly.'
            );
            Url::back();
        }

        if (in_array($task['task_type'], ['recurring_master'], true))
        {
            Flash::error('Šablonu opakovaného úkolu nelze fakturovat.');
            Url::back();
        }

        if ($task['billing_export_id'] !== null) {
            Flash::error(
                'Úkol již byl fakturován nebo exportován.'
            );
            Url::back();
        }

        return $task;
    }

    
 
    /**
     * @param int $taskId
     * @return array<string, mixed>
     */
    public function buildDraftFromTask(int $taskId): array
    {
        $task      = $this->requireTaskForInvoice($taskId);

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

        if ($customer !== null) {
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

                    'minutes' => (
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
     * @param int $orderId
     * @return array<string, mixed>
     */
    public function buildDraftFromWorkOrder(int $orderId): array
    {
        return [];
    }
    /**
     * Vytvoří fakturu z celé zakázky.
     * /
    public function createFromWorkOrder(
        int $companyId,
        int $workOrderId,
        array $data
    ): int {
    }
*/

    /**
     * @param int $taskId
     * @param array<string, mixed> $post
     * @return array{success: bool, errors?: array<string, list<string>>, data?: array<string, mixed>, invoice_id?: int}
     */
    public function invoiceFromTask(
        int $taskId,
        array $post
    ): array
    {
        $errors = [];

        $task = (new TaskModel())->findById($taskId);

        if ($task === null) {
            throw new RuntimeException('Task not found');
        }

        $workOrder = (new WorkOrderModel())->find(
            (int) $task['work_order_id']
        );

        if ($workOrder === null) {
            throw new RuntimeException('Work order not found');
        }

        // =====================
        // VALIDACE
        // =====================

        $data = $this->validateInvoiceData($post);
        // =====================
        // PŘI CHYBĚ
        // =====================


        if ($data['errors'] !== []) {

            $draft = $this->buildDraftFromTask($taskId);

            $draft['invoice']['title']     = $data['title'];
            $draft['invoice']['issued_at'] = $data['issued_at'];
            $draft['invoice']['due_date']  = $data['due_date'];
            $draft['invoice']['note']      = $data['note'];

            $draft['customer'] = $data['customer'];
            $draft['items']    = $data['items'];

            return [
                'success' => false,
                'errors'  => $data['errors'],
                'data'    => $draft,
            ];
        }


        // =====================
        // ULOŽENÍ
        // =====================

        $invoiceId = $this->createInvoice($data);

        return [
            'success'    => true,
            'invoice_id' => $invoiceId,
        ];
    }

/**
 * @param array<string, mixed> $post
 * @return array{title: string, issued_at: string, due_date: string, note: string, customer: array<string, mixed>, items: array<int, array<string, mixed>>, errors: array<string, list<string>>}
 */
private function validateInvoiceData(array $post): array
{
    /** @var array<string, list<string>> $errors */
    $errors = [];

    // ------------------
    // faktura
    // ------------------

    $title = trim((string) ($post['title'] ?? ''));
    $issuedAt = (string) ($post['issued_at'] ?? '');
    $dueDate = (string) ($post['due_date'] ?? '');

    if ($title === '') {
        $errors['title'][] = 'Název faktury je povinný.';
    }

    if ($issuedAt === '') {
        $errors['issued_at'][] = 'Datum vystavení je povinné.';
    }

    if ($dueDate === '') {
        $errors['due_date'][] = 'Datum splatnosti je povinné.';
    }

    // ------------------
    // zákazník
    // ------------------

    $customer = $post['customer'] ?? [];

    // později můžeš přidat validace IČO, DIČ atd.

    // ------------------
    // položky
    // ------------------

    $items = [];

    foreach (($post['items'] ?? []) as $i => $item) {

        $taskId = (int) ($item['task_id'] ?? 0);

        $title = trim((string) ($item['title'] ?? ''));

        $minutes = max(
            0,
            (int) ($item['minutes'] ?? 0)
        );

        $kilometers = max(
            0,
            (float) ($item['kilometers'] ?? 0)
        );

        if ($title === '') {
            $errors["items.$i.title"][] =
                'Název položky je povinný.';
        }

        if ($minutes === 0 && $kilometers === 0) {
            $errors["items.$i"][] =
                'Položka musí obsahovat čas nebo kilometry.';
        }

        $items[] = [
            'task_id'       => $taskId,
            'title'         => $title,
            'minutes'       => $minutes,
            'kilometers'    => $kilometers,
            'visible_time' => ($item['visible_time'] ?? null) === 'on',
            'visible_km'   => ($item['visible_km'] ?? null) === 'on',        ];
    }

    return [
        'title'     => $title,
        'issued_at' => $issuedAt,
        'due_date'  => $dueDate,
        'note'      => (string) ($post['note'] ?? ''),
        'customer'  => $customer,
        'items'     => $items,
        'errors'    => $errors,
    ];
}

    /**
     * Detail faktury.
     * @param int $companyId
     * @param int $invoiceId
     * @return array<string, mixed>
     */
    public function getDetail(
        int $companyId,
        int $invoiceId
    ): array {
        return [];
    }

    /**
     * Seznam faktur.
     * @param int $companyId
     * @return array<string, mixed>
     */
    public function getInvoices(
        int $companyId
    ): array {
         return [];
    }

    /**
     * Storno.
     * @param int $companyId
     * @param int $invoiceId
     * @param int $userId
     * @param ?string $reason
     * @return void
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
     * @param int $companyId
     * @param int $invoiceId
     * @return string
     */
    public function generatePdf(
        int $companyId,
        int $invoiceId
    ): string {
        return '';
    }

    /**
     * Vytvoří fakturu.
     * @param array<string, mixed> $invoice
     * @return int
     */
    private function createInvoice(array $invoice): int
    {
        return 0;
    }

    /*
    private function getTaskSnapshot(
        int $companyId,
        int $taskId
    ): array {
         return [];
    }

    private function getWorkOrderSnapshot(
        int $companyId,
        int $workOrderId
    ): array {
         return [];
    }

    private function getExportSnapshot(
        int $companyId,
        int $exportId
    ): array {
        return [];
    }

    private function renderPdf(
        array $invoice
    ): string {
        return '';
    }
        */
}