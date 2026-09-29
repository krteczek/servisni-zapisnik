<?php
declare(strict_types=1);

namespace App\Services\Invoice;
use App\Core\Auth;
use App\Core\Transaction;
use App\Models\InternalInvoiceModel;
use App\Models\SettingsModel;
use App\Services\Invoice\InvoiceNumberService;
use DateTimeImmutable;
use PDO;
use App\Models\WorkOrderModel;
use App\Models\ContactsModel;
use App\Models\TaskModel;
use App\Models\InternalInvoiceItemModel;
// use App\Models\InternalInvoiceModel;
use App\Services\Settings\SettingsService;
use App\Core\Csrf;
use \RuntimeException;
use \App\Core\Flash;
use \App\Core\Url;
use App\Services\Tasks\TaskType;
use App\Services\Tasks\TaskStatus;

final class InvoiceService
{


    /**
     * @param int $id
     * @return array<string, mixed>
     */
    private function requireTaskForInvoice(int $id): array
    {
        try {
            return (new InvoiceGuard())
                ->assertTaskCanBeInvoiced($id);
        } catch (RuntimeException $e) {
            Flash::error($e->getMessage());
            Url::back();
        }

    }

     /**
     * @param int $taskId
     * @return array<string, mixed>
     */
    public function buildDraftFromTask(int $taskId): array
    {
        $task = $this->requireTaskForInvoice($taskId);

        $taskStats = (new TaskModel())
            ->statsForTasks([$task['id']]);

        $workOrderId = (int) ($task['work_order_id'] ?? 0);

        if ($workOrderId <= 0) {
            throw new RuntimeException('Task has no work order.');
        }

        $workOrder = (new WorkOrderModel())
            ->find($workOrderId);

        if ($workOrder === null) {
            throw new RuntimeException(
                'Work order for task was not found.'
            );
        }

        $contactId = ($workOrder['contact_id'] ?? 0);

        $customer = $contactId > 0
            ? (new ContactsModel())->find($contactId)
            : null;

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
                'title'         => $workOrder['title'],
                'issued_at'     => date('Y-m-d'),
                'due_date'      => date('Y-m-d', strtotime('+' . $dueDays . ' days')),
                'note'          => '',
                'save_customer' => true,
                'contact_id'    => (int) $contactId,
            ],

            'customer' => $customerData,

            'workOrder' => [
                'id'            => $workOrder['id'],
                'title'         => $workOrder['title'],
                'description'   => $workOrder['description'],
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
     * @param int $taskId
     * @param array<string, mixed> $post
     * @return array{success: bool, errors: array<string, list<string>>, data: array<string, mixed>, invoice_id: int}
     */
    public function invoiceFromTask(
        int $taskId,
        array $post
    ): array
    {
        $errors = [];

        $task = (new TaskModel())->findById($taskId);

        if ($task === null) {
            //throw new RuntimeException('Task not found');
            //$errors['global'] = ['Úkol nebyl nalezen...'];
            Flash::error('Úkol nebyl nalezen.');
            Url::back();
        }

        $workOrder = (new WorkOrderModel())->find(
            (int) $task['work_order_id']
        );

        if ($workOrder === null) {
            //throw new RuntimeException('Work order not found');
            //$errors['global'] = ['Zakázka nebyla nalezana...'];
            Flash::error('Zakázka nebyla nalezana...');
            Url::back();
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

            $draft['customer']                 = $data['customer'];
            $draft['items']                    = $data['items'];
            $draft['invoice']['save_customer'] = $data['save_customer'];

            return [
                'success'    => false,
                'invoice_id' => 0,
                'errors'     => $data['errors'],
                'data'       => $draft,
            ];
        }

        // =====================
        // ULOŽENÍ
        // =====================

        (new InvoiceGuard())->assertTaskCanBeInvoiced($taskId);

        $invoiceId = $this->createInvoice($data, $workOrder);

        return [
            'success'    => true,
            'invoice_id' => $invoiceId,
            'errors'     => [],
            'data'       => [],
        ];
    }

/**
 * @param array<string, mixed> $post
 * @return array{
 *   title: string,
 *   issued_at: string,
 *   due_date: string,
 *   note: string,
 *   save_customer: bool,
 *   customer: array<string, mixed>,
 *   items: array<int, array<string, mixed>>,
 *   errors: array<string, list<string>>
 * }
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

    $saveCustomer = isset($post['save_customer']);

    $customer = [
        'company_name' => trim((string) ($post['company_name'] ?? '')),
        'ico'          => trim((string) ($post['ico'] ?? '')),
        'dic'          => trim((string) ($post['dic'] ?? '')),
        'street'       => trim((string) ($post['street'] ?? '')),
        'city'         => trim((string) ($post['city'] ?? '')),
        'zip'           => trim((string) ($post['zip'] ?? '')),
        'country'      => trim((string) ($post['country'] ?? '')),
        'email'        => trim((string) ($post['email'] ?? '')),
        'phone'        => trim((string) ($post['phone'] ?? '')),
    ];

    // později můžeš přidat validace IČO, DIČ atd.

    // ------------------
    // položky
    // ------------------

    $items = [];

    foreach (($post['items'] ?? []) as $i => $item) {

        $taskId = (int) ($item['task_id'] ?? 0);

        $taskTitle = trim((string) ($item['title'] ?? ''));

        $minutes = max(
            0,
            (int) ($item['minutes'] ?? 0)
        );

        $kilometers = max(
            0,
            (float) ($item['kilometers'] ?? 0)
        );

        if ($taskTitle === '') {
            $errors["items.$i.title"][] =
                'Název položky je povinný.';
        }

        if ($minutes === 0 && $kilometers === 0) {
            $errors["items.$i"][] =
                'Položka musí obsahovat čas nebo kilometry.';
        }

        $items[] = [
            'task_id'      => $taskId,
            'title'        => $taskTitle,
            'minutes'      => $minutes,
            'kilometers'   => $kilometers,
            'visible_time' => isset($item['visible_time']),
            'visible_km'   => isset($item['visible_km']),
        ];
    }

    return [
        'title'         => $title,
        'issued_at'     => $issuedAt,
        'due_date'      => $dueDate,
        'note'          => (string) ($post['note'] ?? ''),
        'customer'      => $customer,
        'save_customer' => $saveCustomer,
        'items'         => $items,
        'errors'        => $errors,

    ];
}

    /**
     * Vytvoří interní fakturu.
     *
     * Číslování, snapshot a samotné uložení probíhá
     * v jedné databázové transakci.
     *
     * @param array<string, mixed> $invoice
     * @param array<string, mixed> $workOrder
     * @return int ID vytvořené faktury
     */
    private function createInvoice(
        array $invoice,
        array $workOrder
    ): int {
        $companyId = Auth::companyId();

        if ($companyId === null || $companyId <= 0) {
            throw new RuntimeException('Company context is missing.');
        }

        $issuedAt = (string) ($invoice['issued_at'] ?? '');
        $dueDate  = (string) ($invoice['due_date'] ?? '');

        $issuedDate = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $issuedAt
        );

        $dueDateValue = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $dueDate
        );

        if ($issuedDate === false) {
            throw new RuntimeException('Invalid invoice issue date.');
        }

        if ($dueDateValue === false) {
            throw new RuntimeException('Invalid invoice due date.');
        }

        $year  = (int) $issuedDate->format('Y');
        $month = (int) $issuedDate->format('m');

        $title = trim((string) ($invoice['title'] ?? ''));

        if ($title === '') {
            throw new RuntimeException('Invoice title is empty.');
        }

        $customer = $invoice['customer'] ?? [];
        $items    = $invoice['items'] ?? [];

        if (!is_array($customer)) {
            throw new RuntimeException('Invalid customer data.');
        }

        if (!is_array($items)) {
            throw new RuntimeException('Invalid invoice items.');
        }

        $workOrderId = isset($workOrder['id'])
            ? (int) $workOrder['id']
            : 0;

        if ($workOrderId <= 0) {
            throw new RuntimeException(
                'Work order ID is missing.'
            );
        }

        return Transaction::run(
            function (PDO $db) use (
                $companyId,
                $year,
                $month,
                $issuedAt,
                $dueDate,
                $invoice,
                $customer,
                $items,
                $workOrder,
                $workOrderId
            ): int {
                $settings = new SettingsModel($db);
                $billing  = $settings->getBillingSettings();

                if (!$settings->areRequiredSettingsConfirmed('billing')) {
                    throw new RuntimeException(
                        'Nastavení fakturace nebylo potvrzeno.'
                    );
                }

                $start = (int) ($billing['invoice_number_start'] ?? 0);
                $format = (string) (
                    $billing['invoice_number_format'] ?? ''
                );

                if ($start < 1) {
                    throw new RuntimeException(
                        'Invoice number start is invalid.'
                    );
                }

                if ($format === '') {
                    throw new RuntimeException(
                        'Invoice number format is empty.'
                    );
                }

                $numberService = new InvoiceNumberService();

                $number = $numberService->nextNumber(
                    $db,
                    $companyId,
                    $year,
                    $start
                );

                $invoiceNumber = $numberService->format(
                    $year,
                    $month,
                    $number,
                    $format
                );

                $snapshot = [
                    'invoice' => $invoice,
                    'customer' => $customer,
                    'workOrder' => $workOrder,
                    'items' => $items,
                ];

                $invoiceJson = json_encode(
                    $snapshot,
                    JSON_THROW_ON_ERROR
                );

                $customerName = trim(
                    (string) ($customer['company_name'] ?? '')
                );

                $contactId = null;
                $contactId = isset($workOrder['contact_id'])
                    ? (int) $workOrder['contact_id']
                    : 0;

                if (($invoice['save_customer'] ?? false) === true) {
                    $contactsModel = new ContactsModel($db);

                    $contactData = [
                        'company_name' => trim(
                            (string) ($customer['company_name'] ?? '')
                        ),
                        'ico' => trim(
                            (string) ($customer['ico'] ?? '')
                        ),
                        'dic' => trim(
                            (string) ($customer['dic'] ?? '')
                        ),
                        'street' => trim(
                            (string) ($customer['street'] ?? '')
                        ),
                        'city' => trim(
                            (string) ($customer['city'] ?? '')
                        ),
                        'zip' => trim(
                            (string) ($customer['zip'] ?? '')
                        ),
                        'country' => trim(
                            (string) ($customer['country'] ?? '')
                        ),
                        'email' => trim(
                            (string) ($customer['email'] ?? '')
                        ),
                        'phone' => trim(
                            (string) ($customer['phone'] ?? '')
                        ),
                    ];

                    if ($contactId > 0) {
                        $updated = $contactsModel->update(
                            $contactId,
                            $contactData
                        );

                        if (!$updated) {
                            throw new RuntimeException(
                                'Customer update failed.'
                            );
                        }
                    } else {
                        $contactId = $contactsModel->create(
                            $contactData
                        );

                        if ($contactId <= 0) {
                            throw new RuntimeException(
                                'Customer creation failed.'
                            );
                        }

                        $workOrderModel = new WorkOrderModel($db);

                        $updated = $workOrderModel->update(
                            $workOrderId,
                            [
                                'contact_id' => $contactId,
                            ]
                        );

                        if (!$updated) {
                            throw new RuntimeException(
                                'Work order contact update failed.'
                            );
                        }
                    }
                }

                $createdBy = Auth::id();

                if ($createdBy === null || $createdBy <= 0) {
                    throw new RuntimeException(
                        'Authenticated user is missing.'
                    );
                }

 
                $model = new InternalInvoiceModel($db);

                $invoiceId = $model->create(
                    [
                        'year'           => $year,
                        'number'         => $number,
                        'invoice_number' => $invoiceNumber,
                        'work_order_id'  => $workOrderId,
                        'contact_id'     => $contactId,
                        'customer_name'  => $customerName,
                        'issued_at'      => $issuedAt,
                        'due_date'       => $dueDate,
                        'status'         => InvoiceStatus::DRAFT,
                        'invoice_json'   => $invoiceJson,
                        'created_by'     => $createdBy,
                    ]
                );

                $itemModel = new InternalInvoiceItemModel($db);

                foreach ($items as $item) {
                    $taskId = (int) ($item['task_id'] ?? 0);

                    if ($taskId <= 0) {
                        throw new RuntimeException(
                            'Invoice item task ID is invalid.'
                        );
                    }

                    $itemModel->create([
                        'invoice_id' => $invoiceId,
                        'task_id'    => $taskId,
                    ]);
                }

                return $invoiceId;        },
            'admin'
        );
    }

/**
 * Připraví návrh faktury ze zakázky.
 *
 * Do návrhu zařadí dokončené úkoly zakázky.
 * Zrušené a otevřené úkoly se nefakturují.
 *
 * @param int $workOrderId
 * @return array<string, mixed>
 */
public function buildDraftFromWorkOrder(int $workOrderId): array
{
    if ($workOrderId <= 0) {
        throw new RuntimeException('Work order not found');
    }

    $workOrder = (new WorkOrderModel())->find($workOrderId);

    if ($workOrder === null) {
        throw new RuntimeException('Work order not found');
    }

    $tasks = (new TaskModel())
        ->forWorkOrderWithStats($workOrderId);

    $customer = (new ContactsModel())
        ->find($workOrder['contact_id'] ?? 0);

    $dueDays = (new SettingsService())
        ->getInvoiceDueDays();

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

    $items = [];

    foreach ($tasks as $task) {
        if ($task['status'] !== TaskStatus::DONE) {
            continue;
        }

        if ($task['task_type'] === TaskType::RECURRING_MASTER) {
            continue;
        }

        $stats = $task['stats'];

        $items[] = [
            'task_id'       => $task['id'],
            'title'         => $task['title'],
            'minutes'       => ($stats['total_minutes']),
            'kilometers'    => (float) ($stats['total_km']),
            'visible_title' => true,
            'visible_time'  => true,
            'visible_km'    => true,
        ];
    }

    return [
        'invoice' => [
            'title'     => $workOrder['title'],
            'issued_at' => date('Y-m-d'),
            'due_date'  => date(
                'Y-m-d',
                strtotime('+' . $dueDays . ' days')
            ),
            'note'      => '',
        ],

        'customer' => $customerData,

        'workOrder' => [
            'id'          => $workOrder['id'],
            'title'       => $workOrder['title'],
            'description' => ($workOrder['description']),
        ],

        'items' => $items,
    ];
}

/**
 * Detail faktury.
 *
 * @param int $companyId
 * @param int $invoiceId
 * @return array<string, mixed>
 */
public function getDetail(
    int $companyId,
    int $invoiceId
): array {
    if ($companyId <= 0 || $invoiceId <= 0) {
        throw new RuntimeException(
            'Požadovaná faktura v systému neexistuje.'
        );
    }

    $invoiceModel = new InternalInvoiceModel();

    $invoice = $invoiceModel->find($invoiceId);

    if ($invoice === null) {
        throw new RuntimeException(
            'Požadovaná faktura v systému neexistuje.'
        );
    }

    if ((int) $invoice['company_id'] !== $companyId) {
        throw new RuntimeException(
            'Požadovaná faktura v systému neexistuje.'
        );
    }

    try {
        $snapshot = json_decode(
            $invoice['invoice_json'],
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    } catch (\JsonException $e) {
        throw new RuntimeException(
            'Data faktury jsou poškozena.',
            0,
            $e
        );
    }

    if (!is_array($snapshot)) {
        throw new RuntimeException(
            'Data faktury jsou poškozena.'
        );
    }

    return [
        'id'            => $invoice['id'],
        'invoice_number'=> $invoice['invoice_number'],
        'year'          => $invoice['year'],
        'number'        => $invoice['number'],
        'company_id'    => $invoice['company_id'],
        'work_order_id' => $invoice['work_order_id'],
        'contact_id'    => $invoice['contact_id'],
        'customer_name' => $invoice['customer_name'],
        'issued_at'     => $invoice['issued_at'],
        'due_date'      => $invoice['due_date'],
        'status'        => $invoice['status'],
        'created_by'    => $invoice['created_by'],
        'created_at'    => $invoice['created_at'],

        'invoice'       => $snapshot['invoice'] ?? [],
        'customer'      => $snapshot['customer'] ?? [],
        'workOrder'     => $snapshot['workOrder'] ?? [],
        'items'         => $snapshot['items'] ?? [], 
    ];
}

}