<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Transaction;
use App\Core\Url;
use App\Models\ContactsModel;
use App\Models\InternalInvoiceItemModel;
use App\Models\InternalInvoiceLineModel;
use App\Models\InternalInvoiceModel;
use App\Models\SettingsModel;
use App\Models\TaskModel;
use App\Models\WorkOrderModel;
use App\Services\Settings\SettingsService;
use App\Services\Tasks\TaskStatus;
use App\Services\Tasks\TaskType;
use App\Validators\InvoiceValidator;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use App\Core\Types;
/** @phpstan-import-type TaskBaseRow from Types */
final class InvoiceService
{
    /**
     * @param int $id
     * @return TaskBaseRow
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
     * Připraví návrh faktury z dokončeného úkolu.
     *
     * @return array{
     *     invoice: array<string, mixed>,
     *     customer: array<string, mixed>,
     *     workOrder: array<string, mixed>,
     *     tasks: array<int, array{
     *         task_id:int,
     *         title:string,
     *         minutes:int,
     *         kilometers:float
     *     }>,
     *     lines: array<int, array<string, mixed>>
     * }
     */
    public function buildDraftFromTask(int $taskId): array
    {
        $task = $this->requireTaskForInvoice($taskId);

        $taskModel = new TaskModel();

        $stats = $taskModel->statsForTasks([$taskId]);

        $taskStats = $stats[$taskId] ?? [
            'total_minutes' => 0,
            'total_km' => 0,
        ];

        $workOrder = (new WorkOrderModel())
            ->find( $task['work_order_id']);

        if ($workOrder === null) {
            throw new RuntimeException(
                'Zakázka nebyla nalezena.'
            );
        }

        $contactId = isset($workOrder['contact_id'])
            ? $workOrder['contact_id']
            : 0;

        $customer = $contactId > 0
            ? (new ContactsModel())->find($contactId)
            : null;

        $customerData = $this->buildCustomerData($customer);

        $dueDays = (new SettingsService())
            ->getInvoiceDueDays();

        $issuedAt = date('Y-m-d');

        $dueDate = date(
            'Y-m-d',
            strtotime(
                "+{$dueDays} days",
                strtotime($issuedAt)
            )
        );

        return [
            'invoice' => [
                'title' => $workOrder['title'],
                'issued_at' => $issuedAt,
                'due_date' => $dueDate,
                'note' => '',
                'save_customer' => true,
                'contact_id' => $contactId,
                'currency' => 'CZK',
            ],

            'customer' => $customerData,

            'workOrder' => [
                'id' => $workOrder['id'],
                'title' => $workOrder['title'],
                'description' => (
                    $workOrder['description']
                ),
            ],

            'tasks' => [
                [
                    'task_id' =>  $task['id'],
                    'title' => $task['title'],
                    'minutes' => (
                        $taskStats['total_minutes'] ?? 0
                    ),
                    'kilometers' => (float) (
                        $taskStats['total_km'] ?? 0
                    ),
                ],
            ],

            'lines' => [],
        ];
    }

    /**
     * Zpracuje vytvoření faktury z jednoho úkolu.
     *
     * @param int $taskId
     * @param array<string, mixed> $post
     * @return array{
     *     success: bool,
     *     errors: array<string, list<string>>,
     *     data: array<string, mixed>,
     *     invoice_id: int
     * }
     */
    public function invoiceFromTask(
        int $taskId,
        array $post
    ): array {
        $task = $this->requireTaskForInvoice($taskId);

        $workOrder = (new WorkOrderModel())
            ->find( $task['work_order_id']);

        if ($workOrder === null) {
            throw new RuntimeException(
                'Zakázka nebyla nalezena.'
            );
        }

        $validator = new InvoiceValidator();

        $validated = $validator->validate($post);

        if ($validated['errors'] !== []) {
            $draft = $this->buildDraftFromTask($taskId);

            $draft['invoice']['title'] =
                $validated['title'];

            $draft['invoice']['issued_at'] =
                $validated['issued_at'];

            $draft['invoice']['due_date'] =
                $validated['due_date'];

            $draft['invoice']['note'] =
                $validated['note'];

            $draft['invoice']['contact_id'] =
                $validated['contact_id'];

            $draft['invoice']['save_customer'] =
                $validated['save_customer'];

            $draft['customer'] =
                $validated['customer'];

            $draft['tasks'] =
                $validated['tasks'];

            $draft['lines'] =
                $validated['lines'];

            return [
                'success' => false,
                'invoice_id' => 0,
                'errors' => $validated['errors'],
                'data' => $draft,
            ];
        }

        $normalizer = new InvoiceNormalizer();

        $data = $normalizer->normalize($validated);

        /*
         * Úkol mohl být mezitím fakturován například
         * v jiném okně. Kontrola proto patří těsně
         * před vytvoření faktury.
         */
        (new InvoiceGuard())
            ->assertTaskCanBeInvoiced($taskId);

        $invoiceId = $this->createInvoice(
            $data,
            $workOrder
        );

        return [
            'success' => true,
            'invoice_id' => $invoiceId,
            'errors' => [],
            'data' => [],
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
            throw new RuntimeException(
                'Company context is missing.'
            );
        }

        $issuedAt = (string) (
            $invoice['issued_at'] ?? ''
        );

        $dueDate = (string) (
            $invoice['due_date'] ?? ''
        );

        $issuedDate = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $issuedAt
        );

        $dueDateValue = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $dueDate
        );

        if ($issuedDate === false) {
            throw new RuntimeException(
                'Invalid invoice issue date.'
            );
        }

        if ($dueDateValue === false) {
            throw new RuntimeException(
                'Invalid invoice due date.'
            );
        }

        $year =  (int)$issuedDate->format('Y');
        $month = (int)$issuedDate->format('m');

        $title = trim(
            (string) ($invoice['title'] ?? '')
        );

        if ($title === '') {
            throw new RuntimeException(
                'Invoice title is empty.'
            );
        }

        $customer = $invoice['customer'] ?? [];
        $tasks = $invoice['tasks'] ?? [];
        $lines = $invoice['lines'] ?? [];

        if (!is_array($customer)) {
            throw new RuntimeException(
                'Invalid customer data.'
            );
        }

        if (!is_array($tasks)) {
            throw new RuntimeException(
                'Invalid invoice tasks.'
            );
        }

        if (!is_array($lines)) {
            throw new RuntimeException(
                'Invalid invoice lines.'
            );
        }

        $workOrderId = isset($workOrder['id'])
            ? $workOrder['id']
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
                $tasks,
                $lines,
                $workOrder,
                $workOrderId
            ): int {
                $settings = new SettingsModel($db);

                $billing = $settings
                    ->getBillingSettings();

                if (
                    !$settings
                        ->areRequiredSettingsConfirmed(
                            'billing'
                        )
                ) {
                    throw new RuntimeException(
                        'Nastavení fakturace nebylo potvrzeno.'
                    );
                }

                $start = (
                    $billing['invoice_number_start'] ?? 0
                );

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

                $numberService =
                    new InvoiceNumberService();

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

                /*
                 * Snapshot je historický obraz faktury
                 * v okamžiku jejího vytvoření.
                 */
                $snapshot = [
                    'invoice' => $invoice,
                    'customer' => $customer,
                    'workOrder' => $workOrder,
                    'tasks' => $tasks,
                    'lines' => $lines,
                ];

                $invoiceJson = json_encode(
                    $snapshot,
                    JSON_THROW_ON_ERROR
                );

                $customerName = trim(
                    (string) (
                        $customer['official_name'] ?? ''
                    )
                );

                $contactId = (
                    $invoice['contact_id'] ?? 0
                );

                if (
                    ($invoice['save_customer'] ?? false)
                    === true
                ) {
                    $contactsModel =
                        new ContactsModel($db);

                    $contactData = [
                        'official_name' => trim(
                            (string) (
                                $customer['official_name']
                                ?? ''
                            )
                        ),
                        'ico' => trim(
                            (string) (
                                $customer['ico'] ?? ''
                            )
                        ),
                        'dic' => trim(
                            (string) (
                                $customer['dic'] ?? ''
                            )
                        ),
                        'street' => trim(
                            (string) (
                                $customer['street'] ?? ''
                            )
                        ),
                        'house_number' => trim(
                            (string) (
                                $customer['house_number']
                                ?? ''
                            )
                        ),
                        'orientation_number' => trim(
                            (string) (
                                $customer[
                                    'orientation_number'
                                ] ?? ''
                            )
                        ),
                        'city_part' => trim(
                            (string) (
                                $customer['city_part']
                                ?? ''
                            )
                        ),
                        'city' => trim(
                            (string) (
                                $customer['city'] ?? ''
                            )
                        ),
                        'postal_code' => trim(
                            (string) (
                                $customer['postal_code']
                                ?? ''
                            )
                        ),
                        'country_code' => trim(
                            (string) (
                                $customer['country_code']
                                ?? 'CZ'
                            )
                        ),
                        'delivery_address_1' => trim(
                            (string) (
                                $customer[
                                    'delivery_address_1'
                                ] ?? ''
                            )
                        ),
                        'delivery_address_2' => trim(
                            (string) (
                                $customer[
                                    'delivery_address_2'
                                ] ?? ''
                            )
                        ),
                        'delivery_address_3' => trim(
                            (string) (
                                $customer[
                                    'delivery_address_3'
                                ] ?? ''
                            )
                        ),
                        'email' => trim(
                            (string) (
                                $customer['email'] ?? ''
                            )
                        ),
                        'phone' => trim(
                            (string) (
                                $customer['phone'] ?? ''
                            )
                        ),
                        'bank_account' => trim(
                            (string) (
                                $customer['bank_account']
                                ?? ''
                            )
                        ),
                        'bank_code' => trim(
                            (string) (
                                $customer['bank_code'] ?? ''
                            )
                        ),
                    ];

                    if ($contactId > 0) {
                        $updated =
                            $contactsModel->update(
                                $contactId,
                                $contactData
                            );

                        if (!$updated) {
                            throw new RuntimeException(
                                'Customer update failed.'
                            );
                        }
                    } else {
                        $contactId =
                            $contactsModel->create(
                                $contactData
                            );

                        if ($contactId <= 0) {
                            throw new RuntimeException(
                                'Customer creation failed.'
                            );
                        }

                        $workOrderModel =
                            new WorkOrderModel($db);

                        $updated =
                            $workOrderModel->update(
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

                if (
                    $createdBy === null
                    || $createdBy <= 0
                ) {
                    throw new RuntimeException(
                        'Authenticated user is missing.'
                    );
                }

                $model =
                    new InternalInvoiceModel($db);

                $invoiceId = $model->create(
                    [
                        'year' => $year,
                        'number' => $number,
                        'invoice_number' => $invoiceNumber,
                        'work_order_id' => $workOrderId,
                        'contact_id' => $contactId > 0
                            ? $contactId
                            : null,
                        'customer_name' => $customerName,
                        'issued_at' => $issuedAt,
                        'due_date' => $dueDate,
                        'status' => InvoiceStatus::DRAFT,
                        'invoice_json' => $invoiceJson,
                        'created_by' => $createdBy,
                    ]
                );

                /*
                 * Vazby faktury na zdrojové úkoly.
                 */
                $itemModel =
                    new InternalInvoiceItemModel($db);

                foreach ($tasks as $task) {
                    if (!is_array($task)) {
                        throw new RuntimeException(
                            'Invalid invoice task.'
                        );
                    }

                    $taskId = (
                        $task['task_id'] ?? 0
                    );

                    if ($taskId <= 0) {
                        throw new RuntimeException(
                            'Invoice task ID is invalid.'
                        );
                    }

                    $itemModel->create([
                        'invoice_id' => $invoiceId,
                        'task_id' => $taskId,
                    ]);
                }

                /*
                 * Skutečné fakturační řádky.
                 */
                $lineModel =
                    new InternalInvoiceLineModel($db);

                foreach ($lines as $line) {
                    if (!is_array($line)) {
                        throw new RuntimeException(
                            'Invalid invoice line.'
                        );
                    }

                    $lineModel->create([
                        'invoice_id' => $invoiceId,
                        'description' => (string) (
                            $line['description'] ?? ''
                        ),
                        'quantity' => $line['quantity'] ?? 0,
                        'unit' => (string) (
                            $line['unit'] ?? ''
                        ),
                        'price_unit' => (string) (
                            $line['price_unit'] ?? ''
                        ),
                        'unit_price' => $line['unit_price']
                            ?? 0,
                        'total' => $line['total'] ?? 0,
                        'currency' => (string) (
                            $line['currency'] ?? 'CZK'
                        ),
                    ]);
                }

                return $invoiceId;
            },
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
    public function buildDraftFromWorkOrder(
        int $workOrderId
    ): array {
        if ($workOrderId <= 0) {
            throw new RuntimeException(
                'Work order not found'
            );
        }

        $workOrder = (new WorkOrderModel())
            ->find($workOrderId);

        if ($workOrder === null) {
            throw new RuntimeException(
                'Work order not found'
            );
        }

        $tasks = (new TaskModel())
            ->forWorkOrderWithStats($workOrderId);

        $contactId = (
            $workOrder['contact_id'] ?? 0
        );

        $customer = $contactId > 0
            ? (new ContactsModel())->find($contactId)
            : null;

        $dueDays = (new SettingsService())
            ->getInvoiceDueDays();

        $customerData =
            $this->buildCustomerData($customer);

        $draftTasks = [];

        foreach ($tasks as $task) {
            if ($task['status'] !== TaskStatus::DONE) {
                continue;
            }

            if (
                $task['task_type']
                === TaskType::RECURRING_MASTER
            ) {
                continue;
            }

            $stats = $task['stats'];

            $draftTasks[] = [
                'task_id' => $task['id'],
                'title' => $task['title'],
                'minutes' => (
                    $stats['total_minutes']
                ),
                'kilometers' => (float) (
                    $stats['total_km']
                ),
            ];
        }

        return [
            'invoice' => [
                'title' =>  $workOrder['title'],
                'issued_at' => date('Y-m-d'),
                'due_date' => date(
                    'Y-m-d',
                    strtotime(
                        '+' . $dueDays . ' days'
                    )
                ),
                'note' => '',
                'save_customer' => true,
                'contact_id' => $contactId,
                'currency' => 'CZK',
            ],

            'customer' => $customerData,

            'workOrder' => [
                'id' => $workOrder['id'],
                'title' => $workOrder['title'],
                'description' => (
                    $workOrder['description']               ),
            ],

            'tasks' => $draftTasks,

            'lines' => [],
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
        if (
            $companyId <= 0
            || $invoiceId <= 0
        ) {
            throw new RuntimeException(
                'Požadovaná faktura v systému neexistuje.'
            );
        }

        $invoiceModel =
            new InternalInvoiceModel();

        $invoice =
            $invoiceModel->find($invoiceId);

        if ($invoice === null) {
            throw new RuntimeException(
                'Požadovaná faktura v systému neexistuje.'
            );
        }

        if (
            $invoice['company_id']
            !== $companyId
        ) {
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
            'id' => $invoice['id'],
            'invoice_number' =>
                $invoice['invoice_number'],
            'year' => $invoice['year'],
            'number' => $invoice['number'],
            'company_id' => $invoice['company_id'],
            'work_order_id' =>
                $invoice['work_order_id'],
            'contact_id' => $invoice['contact_id'],
            'customer_name' =>
                $invoice['customer_name'],
            'issued_at' => $invoice['issued_at'],
            'due_date' => $invoice['due_date'],
            'status' => $invoice['status'],
            'created_by' => $invoice['created_by'],
            'created_at' => $invoice['created_at'],

            'invoice' =>
                $snapshot['invoice'] ?? [],

            'customer' =>
                $snapshot['customer'] ?? [],

            'workOrder' =>
                $snapshot['workOrder'] ?? [],

            'tasks' =>
                $snapshot['tasks'] ?? [],

            'lines' =>
                $snapshot['lines'] ?? [],
        ];
    }

    /**
     * Připraví kompletní strukturu zákazníka pro formulář.
     *
     * @param array<string, mixed>|null $customer
     * @return array<string, mixed>
     */
    private function buildCustomerData(
        ?array $customer
    ): array {
        $customerData = [
            'official_name' => '',
            'ico' => '',
            'dic' => '',
            'street' => '',
            'house_number' => '',
            'orientation_number' => '',
            'city_part' => '',
            'city' => '',
            'postal_code' => '',
            'country_code' => 'CZ',
            'delivery_address_1' => '',
            'delivery_address_2' => '',
            'delivery_address_3' => '',
            'email' => '',
            'phone' => '',
            'bank_account' => '',
            'bank_code' => '',
        ];

        if ($customer !== null) {
            $customerData = array_merge(
                $customerData,
                $customer
            );
        }

        return $customerData;
    }
}