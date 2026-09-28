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
use App\Models\InternalInvoiceModel;
use App\Services\Invoice\InvoiceService;
use App\Services\Settings\SettingsService;
use Throwable;
use App\Core\LoggerHolder;
use App\Core\Auth;
use RuntimeException;

class InvoiceController extends Controller
{
    private InvoiceService $invoice;

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->invoice = new InvoiceService();
    }

    /**
     * /billing/invoice/create/task/123 GET
     */
public function createTask(int $id): string
{
    try {
        $this->view->data =
            $this->invoice->buildDraftFromTask($id);

        return $this->render('invoices/create-from-task');

    } catch (Throwable $e) {

        LoggerHolder::get()->error(
            'InvoiceController.createTask FAILED',
            [
                'task_id' => $id,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]
        );

        Flash::error(
            'Fakturu se nepodařilo připravit.'
        );

        Url::back();
    }
}


    /**
     * POST
     */
    public function storeTask(int $id): string
    {
        if ($_POST === []) {
            Flash::error('Neplatná žádost. Musíte vyplnit požadovaná pole...');
            Url::back();
        }

        $this->checkCsrf();

        try {
            /** 
             * vrací:
             * return [
             *      'success'    => true, bool
             *      'invoice_id' => $invoiceId, int
             *      'errors'     => [], array list
             *      'data'       => [], array list
             * ];

             */
            $result = $this->invoice->invoiceFromTask(
                $id,
                $_POST
            );

            if ($result['success'] === false) {

                $this->view->errors = $result['errors'];
                $this->view->data   = $result['data'];

                return $this->render('invoices/create-task');
            }

            Flash::success('Návrh faktury byl vytvořen.');

            Url::redirect(
                '/{tenant}/billing/invoice/' . $result['invoice_id'] . '/detail/#main'
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
     * seznam návrhů a faktur
     */
public function index(): string
{
    $settingsService = new SettingsService();

    $this->view->invoiceSettingsConfirmed =
        $settingsService->areInvoiceSettingsConfirmed();

    if ($this->view->invoiceSettingsConfirmed === true) {
        $model = new InternalInvoiceModel();

        $this->view->invoices = $model->all();
    }

    return $this->render('invoices/index');
}


 /**
 * Detail faktury
 */
public function detail(int $id): string
{
    try {
        $companyId = Auth::companyId();

        if ($companyId === null || $companyId <= 0) {
            Flash::error(
                'Nelze určit pracovní prostor.'
            );

            Url::redirect('/{tenant}/billing/invoices/#main');
        }

        $this->view->invoice = $this->invoice->getDetail(
            $companyId,
            $id
        );

        return $this->render('invoices/detail');

    } catch (RuntimeException $e) {

        LoggerHolder::get()->warning(
            'Invoice detail unavailable',
            [
                'invoice_id' => $id,
                'message'    => $e->getMessage(),
            ]
        );

        Flash::error(
            $e->getMessage()
        );

        Url::redirect('/{tenant}/billing/invoices/#main');
    } catch (Throwable $e) {

        LoggerHolder::get()->error(
            'InvoiceController.detail FAILED',
            [
                'invoice_id' => $id,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]
        );

        Flash::error(
            'Detail faktury se nepodařilo načíst.'
        );

        Url::redirect('/{tenant}/billing/invoices/#main');
    }
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