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
use throwable;
use App\Core\LoggerHolder;

class InvoiceController extends Controller
{
    private InvoiceService $invoice;
    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->invoice = new InvoiceService();
    }

 
    /** 
     * /billing/invoice/create/work-order/456
     * momentálně ještě nepoužito
     * /
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

    } */


    /** 
     * /billing/invoice/create/export/789
     * momentálně ještě nepoužito
     * /
    private function requireExportForInvoice(int $id): array
    {
        return [];
    }
        */

    /**
     * /billing/invoice/create/task/123 GET
     */
    public function createTask(int $id): string
    {
        $this->view->data = $this->invoice->buildDraftFromTask($id);

        return $this->render('invoices/create-task');
    }


        /**
     * POST
     */
    public function storeTask(int $id): string
    {
        if (empty($_POST)) {
            Flash::error('Neplatná žádost.');
            Url::back();
        }

        $this->checkCsrf();

        try {

            $result = $this->invoice->invoiceFromTask(
                $id,
                $_POST
            );

            if (!$result['success']) {

                $this->view->errors = $result['errors'];
                $this->view->data   = $result['data'];

                return $this->render('invoices/create-task');
            }

            Flash::success('Faktura byla vytvořena.');

            Url::redirect(
                '/{tenant}/billing/invoice/' . $result['invoice_id'] . '/#main'
            );

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceController.storeTask FAILED',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );

            Flash::error(
                'Fakturu se nepodařilo vytvořit.'
            );

            Url::back();
        }
    }


    /**
     * /billing/invoice/create/work-order/456
     * /
    public function createWorkOrder(int $id): string
    {
        $task = $this->requireTaskForInvoice($id);
        //$ok = $this->invoice->createFromTask($task);
        return $this->render('invoices/create-work-order');
    }
        */

    public function createWorkOrder(int $id): string
    {
        $this->view->data = $this->invoice->buildDraftFromWorkOrder($id);

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