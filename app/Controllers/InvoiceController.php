<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ViewContext;
use App\Core\Flash;
use App\Core\Csrf;
use App\Core\Url;
use App\Models\ContactsModel;
use App\Models\TaskModel;
use App\Models\WorkOrderModel;
use App\Services\Invoice\InvoiceService;

class InvoiceController extends Controller
{
    private InvoiceService $invoice;
    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->invoice = new InvoiceService();
    }

    private function requireTaskForInvoice(int $id): array
    {
        if ($id <= 0) {
            Flash::error('Požadovaný úkol neexistuje');
            Url::back();
        }

        $task = (new TaskModel())->findById($id);

        if (!$task) {
            Flash::error('Požadovaný úkol neexistuje');
            Url::back();
        }

        if ($task['status'] !== 'done') {
            Flash::error(
                'Fakturovat lze pouze dokončené úkoly.'
            );
            Url::back();
        }

        if ((int) ($task['is_recurring'] ?? 0) === 1) {
            Flash::error(
                'Opakovaný úkol nelze fakturovat přímo.'
            );
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

    private function requireWorkOrderForInvoice(int $id): array
    {
        if ($id <= 0) {
            Flash::error('Požadovaná zakázka neexistuje');
            Url::back();
        }

        $wo = (new WorkOrderModel())->find($id);

        if (!$wo) {
            Flash::error('Požadovaná zakázka neexistuje');
            Url::back();
        }

        if ($wo['status'] !== 'done') {
            Flash::error(
                'Fakturovat lze pouze dokončené zakázky.'
            );
            Url::back();
        }

        if ($wo['billing_export_id'] !== null) {
            Flash::error(
                'Úkol již byl fakturován nebo exportován.'
            );
            Url::back();
        }

        return $wo;

    }

    private function requireExportForInvoice(int $id): array
    {
        return [];
    }

    /**
     * /billing/invoice/create/task/123
     */
    public function createTask(int $id): string
    {
        $task      = $this->requireTaskForInvoice($id);
        $this->view->data = $this->invoice->buildDraftFromTask($task);

        return $this->render('invoices/create-task');
    }

    /**
     * /billing/invoice/create/work-order/456
     */
    public function createWorkOrder(int $id): string
    {
        $task = $this->requireTaskForInvoice($id);
        $ok = $this->invoice->createFromTask($task);
        return $this->render('invoices/create-work-order');
    }

    /**
     * /billing/invoice/create/export/789
     */
    public function createExport(int $id): string
    {
        return $this->render('billing/invoice/create-export');
    }

    /**
     * POST
     */
    public function store(): void
    {
    }

    /**
     * seznam faktur
     */
    public function index(): string
    {
        return $this->render('billing/invoice/index');
    }

    /**
     * detail faktury
     */
    public function detail(int $id): string
    {
        return $this->render('billing/invoice/detail');
    }

    /**
     * pdf
     */
    public function pdf(int $id): void
    {
    }

    /**
     * storno
     */
    public function cancel(int $id): void
    {
    }
}