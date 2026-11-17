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
use App\Models\TaskModel;
use App\Models\WorkOrderModel;
use App\Models\CompanyModel;
use App\Services\Settings\SettingsService;
use App\Services\Tasks\TaskStatus;
use App\Services\Tasks\TaskType;
use App\Validators\InvoiceValidator;
use PDO;
use RuntimeException;
use App\Core\Types;
use App\Validators\Validator;
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
            Flash::error('Zakázka nebyla nalezena.');
            Url::back();
        }

        $contactId = isset($workOrder['contact_id'])
            ? $workOrder['contact_id']
            : 0;

        $customer = $contactId > 0
            ? (new ContactsModel())->find($contactId)
            : null;

        $supplier = (new CompanyModel())->billingData($task['company_id']);

        if ($supplier === null) {
            Flash::error('Údaje dodavatele nebyly nalezeny.');
            Url::back();
        }

        $customerData = $this->buildCustomerData($customer);

        return [
            'invoice' => [
                'title' => $workOrder['title'],
                'note' => '',
                'save_customer' => true,
                'contact_id' => $contactId,
                'currency' => 'CZK',
            ],

            'supplier' => $supplier,

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
        array $post,
        Validator $validator
    ): array {
        $task = $this->requireTaskForInvoice($taskId);

        $workOrder = (new WorkOrderModel())
            ->find( $task['work_order_id']);

        if ($workOrder === null) {
            throw new RuntimeException(
                'Zakázka nebyla nalezena.'
            );
        }

        $invoiceValidator = new InvoiceValidator($validator);

        $validated = $invoiceValidator->validate($post);

        if ($validated['errors'] !== []) {
            $draft = $this->buildDraftFromTask($taskId);

            $draft['invoice']['title'] =
                $validated['title'];
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
     * Podporuje dvě cesty:
     *  - faktura z podkladu (workOrder + tasks)
     *  - faktura bez podkladu (workOrder = null, tasks = [])
     *
     * @param array<string, mixed> $invoice
     * @param array<string, mixed>|null $workOrder
     * @return int ID vytvořené faktury
     */
    private function createInvoice(
        array $invoice,
        ?array $workOrder
    ): int {
        $companyId = Auth::companyId();

        if ($companyId === null || $companyId <= 0) {
            throw new RuntimeException(
                'Company context is missing.'
            );
        }

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

        /*
         * Faktura bez podkladu:
         *  - work_order_id může být null
         *  - tasks může být prázdné pole
         *  - internal_invoice_items zůstane prázdná
         */
        $workOrderId = null;

        if (is_array($workOrder) && isset($workOrder['id'])) {
            $workOrderId = (int) $workOrder['id'];

            if ($workOrderId <= 0) {
                $workOrderId = null;
            }
        }

        return Transaction::run(
            function (PDO $db) use (
                $invoice,
                $customer,
                $tasks,
                $lines,
                $workOrder,
                $workOrderId
            ): int {

                $number = null;
                $invoiceNumber = null;

                /*
                 * Snapshot je historický obraz faktury
                 * v okamžiku jejího vytvoření.
                 *
                 * Pro fakturu bez podkladu bude workOrder null
                 * a tasks prázdné pole.
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

                        /*
                         * Pokud faktura vzniká z podkladu
                         * a zákazník byl právě vytvořen,
                         * navážeme ho i na zakázku.
                         *
                         * Pro fakturu bez podkladu se nic neděje.
                         */
                        if ($workOrderId !== null) {
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
                       
                        'year' => null,
                        'number' => null,
                        'invoice_number' => null,
                        'issued_at' => null,
                        'due_date' => null,                      
                        'work_order_id' => $workOrderId,
                        'contact_id' => $contactId > 0
                            ? $contactId
                            : null,
                        'customer_name' => $customerName,
                        'status' => InvoiceStatus::DRAFT,
                        'invoice_json' => $invoiceJson,
                        'created_by' => $createdBy,
                    ]
                );

                /*
                 * Vazby faktury na zdrojové úkoly.
                 *
                 * Pro fakturu bez podkladu je $tasks prázdné
                 * pole a tato část se přeskočí.
                 */
                if ($tasks !== []) {
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
                }

                /*
                 * Skutečné fakturační řádky.
                 *
                 * I pro fakturu bez podkladu zde mohou být
                 * ručně zadané řádky.
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
            'work_order_id' => $invoice['work_order_id'],
            'contact_id' => $invoice['contact_id'],
            'customer_name' => $invoice['customer_name'],
            'issued_at' => $invoice['issued_at'],
            'issued_on' => $invoice['issued_on'],
            'issued_by' => $invoice['issued_by'],
            'due_date' => $invoice['due_date'],
            'status' => $invoice['status'],
            'created_by' => $invoice['created_by'],
            'created_at' => $invoice['created_at'],

            'invoice' => $snapshot['invoice'] ?? [],

            'customer' => $snapshot['customer'] ?? [],

            'workOrder' => $snapshot['workOrder'] ?? [],

            'tasks' => $snapshot['tasks'] ?? [],

            'lines' => $snapshot['lines'] ?? [],
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

    /**
     * Vydá draft fakturu (draft → issued).
     *
     * Před vydáním znovu ověří, že žádný z navázaných
     * úkolů mezitím nezískal jinou aktivní fakturu.
     *
     * Draft totiž úkol neblokuje (viz
     * InternalInvoiceItemModel::isTaskActivelyInvoiced),
     * takže task mohl být mezitím fakturován jinde.
     *
     * @param int $companyId
     * @param int $invoiceId
     * @return void
     */
public function issue(int $companyId, int $invoiceId): void
{
    if ((int) Auth::companyId() !== $companyId) {
        throw new \RuntimeException('Neplatná firma.');
    }

    Transaction::run(function (\PDO $db) use ($companyId, $invoiceId): bool {

        // Zamkneme firmu. Tím serializujeme vystavování faktur
        // v rámci jedné firmy.
        $companyModel = new CompanyModel($db);

        if ($companyModel->lockForUpdate($companyId) === null) {
            throw new \RuntimeException('Firma nebyla nalezena.');
        }

        // Načteme návrh faktury.
        $invoiceModel = new InternalInvoiceModel($db);
        $invoice = $invoiceModel->find($invoiceId);

        if (
            !$invoice
            || (int) $invoice['company_id'] !== $companyId
            || $invoice['status'] !== InvoiceStatus::DRAFT
        ) {
            throw new \RuntimeException('Návrh faktury nelze vystavit.');
        }

        // Znovu ověříme fakturovatelnost všech navázaných úkolů.
        $itemModel = new InternalInvoiceItemModel($db);
        $taskModel = new TaskModel($db);

        $items = $itemModel->forInvoice($invoiceId);

        foreach ($items as $item) {
            $taskId = (int) ($item['task_id'] ?? 0);

            if ($taskId <= 0) {
                throw new \RuntimeException(
                    'Návrh obsahuje neplatnou položku.'
                );
            }

            $task = $taskModel->findById($taskId);

            if (
                $task === null
                || $task['status'] !== TaskStatus::DONE
                || $task['task_type'] === TaskType::RECURRING_MASTER
            ) {
                throw new \RuntimeException(
                    'Některý z úkolů již nelze fakturovat.'
                );
            }

            if ($itemModel->isTaskActivelyInvoiced($taskId)) {
                throw new \RuntimeException(
                    'Některý z úkolů je již součástí jiné vydané faktury.'
                );
            }
        }
        // Načteme a ověříme fakturační nastavení.
        $settings = new SettingsService($db);

        if (!$settings->areInvoiceSettingsConfirmed()) {
            throw new \RuntimeException(
                'Fakturační nastavení nejsou potvrzena.'
            );
        }

        $dueDays = $settings->getInvoiceDueDays();
        $numberStart = $settings->getInvoiceNumberStart();
        $numberFormat = $settings->getInvoiceNumberFormat();

        if (!is_numeric($dueDays) || (int) $dueDays < 0) {
            throw new \RuntimeException('Neplatná doba splatnosti faktury.');
        }

        if (!is_numeric($numberStart) || (int) $numberStart < 1) {
            throw new \RuntimeException('Neplatný počáteční stav číselné řady.');
        }

        $dueDays = (int) $dueDays;
        $numberStart = (int) $numberStart;

        $numberService = new InvoiceNumberService();

        if (!$numberService->isValidFormat($numberFormat)) {
            throw new \RuntimeException('Neplatný formát číslování faktur.');
        }

        // Datum a čas vystavení.
        $now = new \DateTimeImmutable();

        $issuedAt = $now->format('Y-m-d');
        $issuedOn = $now->format('Y-m-d H:i:s');

        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');

        // Datum splatnosti.
        $dueDate = $now
            ->setTime(0, 0)
            ->modify("+{$dueDays} days")
            ->format('Y-m-d');

        // Vystavující uživatel.
        $issuedBy = Auth::id();

        if (!$issuedBy || (int) $issuedBy <= 0) {
            throw new \RuntimeException('Nelze určit vystavujícího uživatele.');
        }

        // Přidělíme číslo faktury.
        $number = $numberService->nextNumber(
            $db,
            $companyId,
            $year,
            $numberStart
        );

        $invoiceNumber = $numberService->format(
            $year,
            $month,
            $number,
            $numberFormat
        );

        // Uložíme vystavenou fakturu.
        $updated = $invoiceModel->update($invoiceId, [
            'year' => $year,
            'number' => $number,
            'invoice_number' => $invoiceNumber,
            'issued_at' => $issuedAt,
            'issued_on' => $issuedOn,
            'issued_by' => $issuedBy,
            'due_date' => $dueDate,
            'status' => InvoiceStatus::ISSUED,
        ]);

        if (!$updated) {
            throw new \RuntimeException('Fakturu se nepodařilo vystavit.');
        }

        return true;

    }, 'admin');
}


    /**
     * Stornuje vydanou fakturu (issued → cancelled).
     *
     * Varianta 1: mění se pouze stav, nevzniká opravný doklad.
     * Po stornu lze zdrojové úkoly fakturovat znovu
     * (cancelled se v isTaskActivelyInvoiced nepočítá).
     *
     * @param int $companyId
     * @param int $invoiceId
     * @return void
     */
    public function cancel(int $companyId, int $invoiceId): void
    {
        if ($companyId <= 0 || $invoiceId <= 0) {
            throw new RuntimeException(
                'Požadovaná faktura v systému neexistuje.'
            );
        }

        if (Auth::companyId() !== $companyId) {
            throw new RuntimeException(
                'Požadovaná faktura v systému neexistuje.'
            );
        }

        $model = new InternalInvoiceModel();

        if (!$model->cancelIssued($invoiceId, $companyId)) {
            throw new RuntimeException(
                'Fakturu se nepodařilo stornovat. '
                . 'Faktura neexistuje nebo není ve stavu vydaná.'
            );
        }
    }
}