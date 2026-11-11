<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ViewContext;
use App\Core\Flash;
use App\Core\Csrf;
use App\Core\Url;
use App\Models\ContactsModel;


use App\Models\InternalInvoiceModel;
use App\Services\Invoice\InvoiceService;
use App\Services\Settings\SettingsService;
use Throwable;
use App\Core\LoggerHolder;
use App\Core\Auth;
use RuntimeException;
use App\Services\Invoice\InvoiceStatus;
use App\Models\CompanyModel;

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
public function createFromTask(int $id): string
{
    $this->setSessionCheck('task_id', $id);
    try {
        $this->view->data = $this->invoice->buildDraftFromTask($id);
        $this->view->contacts = (new ContactsModel())->all();
        // dc($this->view->data);
        return $this->render('invoices/create-from-task');

    } catch (Throwable $e) {

        LoggerHolder::get()->error(
            'InvoiceController.createFromTask FAILED',
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
    public function storeFromTask(int $id): string
    {
        $this->confirmSessionCheck('task_id', $id, '/{tenant}/billing/invoice/#main');
        
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
                $_POST,
                $this->validator
            );

            if ($result['success'] === false) {
                $this->setSessionCheck('task_id', $id);

                $this->view->data   = $result['data'];
                $this->view->contacts = (new ContactsModel())->all();
                return $this->render('invoices/create-from-task');
            }

            Flash::success('Návrh faktury byl vytvořen.');

            Url::redirect(
                '/{tenant}/billing/invoice/' . $result['invoice_id'] . '/detail/#main'
            );

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceController.storeFromTask FAILED',
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
     * Vydá draft fakturu (POST).
     *
     * POST /{tenant}/billing/invoice/{id}/issue
     */
    public function issue(int $id): string
    {
        if ($_POST === []) {
            Flash::error('Neplatná žádost.');
            Url::back();
        }

        $this->checkCsrf();

        try {
            $companyId = Auth::companyId();

            if ($companyId === null || $companyId <= 0) {
                Flash::error(
                    'Nelze určit pracovní prostor.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoices/#main'
                );
            }

            $this->invoice->issue($companyId, $id);

            Flash::success('Faktura byla vydána.');

            Url::redirect(
                '/{tenant}/billing/invoice/' . $id . '/detail/#main'
            );

        } catch (RuntimeException $e) {

            LoggerHolder::get()->warning(
                'InvoiceController.issue rejected',
                [
                    'invoice_id' => $id,
                    'message'    => $e->getMessage(),
                ]
            );

            Flash::error($e->getMessage());

            Url::redirect(
                '/{tenant}/billing/invoice/' . $id . '/detail/#main'
            );

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceController.issue FAILED',
                [
                    'invoice_id' => $id,
                    'message'    => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ]
            );

            Flash::error('Fakturu se nepodařilo vydat.');

            Url::redirect(
                '/{tenant}/billing/invoice/' . $id . '/detail/#main'
            );
        }
    }

    /**
     * pdf
     */
     /**
     * Vygeneruje PDF faktury.
     *
     * GET /{tenant}/billing/invoice/{id}/pdf
     *
     * PDF je dostupné pro stavy issued, paid a cancelled.
     * Pro cancelled se vykreslí watermark "STORNO".
     */
    public function pdf(int $id): void
    {
        try {
            $companyId = Auth::companyId();

            if ($companyId === null || $companyId <= 0) {
                Flash::error(
                    'Nelze určit pracovní prostor.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoices/#main'
                );
            }

            $invoice = $this->invoice->getDetail(
                $companyId,
                $id
            );

            $status = (string) ($invoice['status'] ?? '');

            if (
                !in_array(
                    $status,
                    [
                        InvoiceStatus::ISSUED,
                        InvoiceStatus::PAID,
                        InvoiceStatus::CANCELLED,
                    ],
                    true
                )
            ) {
                Flash::error(
                    'PDF lze stáhnout pouze pro vydanou fakturu.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoice/' . $id . '/detail/#main'
                );
            }

            /*
             * Dodavatel není součástí snapshotu (snapshot
             * obsahuje jen data z formuláře). Dotáhneme
             * ho z CompanyModel::billingData().
             */
            $supplier = (new CompanyModel())
                ->billingData($companyId);

            if ($supplier === null) {
                Flash::error(
                    'Údaje dodavatele nebyly nalezeny.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoice/' . $id . '/detail/#main'
                );
            }

            /*
             * HTML šablona PDF.
             *
             * Renderujeme do bufferu, protože Dompdf
             * očekává hotový HTML string.
             */
            $this->view->invoice  = $invoice;
            $this->view->supplier = $supplier;

            ob_start();
            require __DIR__ . '/../../views/invoices/pdf.php';
            $html = (string) ob_get_clean();

            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $invoiceNumber = (string) (
                $invoice['invoice_number'] ?? $id
            );

            $filename = 'faktura-' . $invoiceNumber . '.pdf';

            $dompdf->stream($filename, [
                'Attachment' => true,
            ]);

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceController.pdf FAILED',
                [
                    'invoice_id' => $id,
                    'message'    => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ]
            );

            Flash::error(
                'PDF se nepodařilo vygenerovat.'
            );

            Url::redirect(
                '/{tenant}/billing/invoice/' . $id . '/detail/#main'
            );
        }
    }
    /**
     * Stornuje vydanou fakturu (POST).
     *
     * POST /{tenant}/billing/invoice/{id}/cancel
     */
    public function cancel(int $id): string
    {
        if ($_POST === []) {
            Flash::error('Neplatná žádost.');
            Url::back();
        }

        $this->checkCsrf();

        try {
            $companyId = Auth::companyId();

            if ($companyId === null || $companyId <= 0) {
                Flash::error(
                    'Nelze určit pracovní prostor.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoices/#main'
                );
            }

            $this->invoice->cancel($companyId, $id);

            Flash::success('Faktura byla stornována.');

            Url::redirect(
                '/{tenant}/billing/invoice/' . $id . '/detail/#main'
            );

        } catch (RuntimeException $e) {

            LoggerHolder::get()->warning(
                'InvoiceController.cancel rejected',
                [
                    'invoice_id' => $id,
                    'message'    => $e->getMessage(),
                ]
            );

            Flash::error($e->getMessage());

            Url::redirect(
                '/{tenant}/billing/invoice/' . $id . '/detail/#main'
            );

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceController.cancel FAILED',
                [
                    'invoice_id' => $id,
                    'message'    => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ]
            );

            Flash::error('Fakturu se nepodařilo stornovat.');

            Url::redirect(
                '/{tenant}/billing/invoice/' . $id . '/detail/#main'
            );
        }
    }
 }