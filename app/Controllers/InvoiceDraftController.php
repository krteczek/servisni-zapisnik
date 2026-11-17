<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ViewContext;
use App\Core\Flash;
use App\Core\Csrf;
use App\Core\Url;
use App\Core\Session;
use App\Models\ContactsModel;
use App\Services\Ares\AjaxStatus;


use App\Models\InternalInvoiceModel;
use App\Services\Invoice\InvoiceService;
use App\Services\Settings\SettingsService;
use Throwable;
use App\Core\LoggerHolder;
use App\Core\Auth;
use RuntimeException;
use App\Services\Invoice\InvoiceStatus;
use App\Models\CompanyModel;
use App\Services\Invoice\InvoicePdfService;

class InvoiceDraftController extends Controller
{
    private InvoiceService $invoice;
    private InvoicePdfService $pdfService;

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->invoice = new InvoiceService();
        $this->pdfService = new InvoicePdfService();
    }



    /**
     * /{tenant}/billing/invoice/draft/create/from-task/{id:\d+} GET
     */
    public function createFromTask(int $id): string
    {
        $this->setSessionCheck('task_id', $id);
        try {
            $this->view->data = $this->invoice->buildDraftFromTask($id);
            $this->view->contacts = (new ContactsModel())->all();
            AjaxStatus::set();
            return $this->render('invoices/create-from-task');

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceDraftController.createFromTask FAILED',
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
            AjaxStatus::set();
            Flash::error('Neplatná žádost. Musíte vyplnit požadovaná pole...');
            Url::back();
        }

        $this->checkCsrf();
        if($this->hasErrors()) {
            AjaxStatus::set();
            Flash::error('Neplatná žádost. Musíte vyplnit požadovaná pole...');
            Url::back();
        }
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

            AjaxStatus::consume();
            Flash::success('Návrh faktury byl vytvořen.');

            Url::redirect(
                '/{tenant}/billing/invoice/draft/' . $result['invoice_id'] . '/detail/#main'
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

    /**
     * /{tenant}/billing/invoice/draft/create/from-work-order/{id:\d+} GET
     */
    public function createWorkOrder(int $id): string
    {
        $this->view->data = $this->invoice->buildDraftFromWorkOrder($id);

        return $this->render('invoices/create-work-order');
    }       
/**
 * '/{tenant}/billing/invoice/draft/create/from-work-order/{id:\d+} POST
 *   
    public function storeWorkOrder(int $id): string
    {
        if ($_POST === []) {
            Flash::error('Neplatná žádost. Musíte vyplnit požadovaná pole...');
            Url::back();
        }

        $this->checkCsrf();

        try {
            $result = $this->invoice->invoiceFromWorkOrder(
                $id,
                $_POST,
                $this->validator
            );

            if ($result['success'] === false) {
                $this->view->data   = $result['data'];
                return $this->render('invoices/create-work-order');
            }

            Flash::success('Návrh faktury byl vytvořen.');

            Url::redirect(
                '/{tenant}/billing/invoice/draft/' . $result['invoice_id'] . '/detail/#main'
            );

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceController.storeWorkOrder FAILED',
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
*/
     
    /**
     * Detail faktury
     * /{tenant}/billing/invoice/draft/{id:\d+}/detail GET
     */
    public function detail(int $id): string
    {
        try {
            $companyId = Auth::companyId();

            if ($companyId === null || $companyId <= 0) {
                Flash::error(
                    'Nelze určit pracovní prostor.'
                );

                Url::redirect('/{tenant}/billing/invoice/#main');
            }

            $this->view->invoice = $this->invoice->getDetail(
                $companyId,
                $id
            );
            
            if (($this->view->invoice['status'] ?? null) !== InvoiceStatus::DRAFT) {
                Flash::error(
                    'Detail lze zobrazit pouze u návrhu faktury.'
                );

                Url::redirect('/{tenant}/billing/invoice/#main');
            }
            return $this->render('invoices/draftDetail');

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

            Url::redirect('/{tenant}/billing/invoice/#main');
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

            Url::redirect('/{tenant}/billing/invoice/#main');
        }

    }


    /**
     * Editace návrhu faktury.
     *
     * /{tenant}/billing/invoice/draft/{id:\d+}/edit GET 
     */
    public function edit(int $id): string
    {
        try {
            $companyId = Auth::companyId();

            if ($companyId === null || $companyId <= 0) {
                Flash::error('Nelze určit pracovní prostor.');
                Url::redirect('/{tenant}/billing/invoice/#main');
            }

            // Načtení faktury včetně snapshotu.
            $invoice = $this->invoice->getDetail(
                $companyId,
                $id
            );

            // Editovat lze pouze návrh.
            if (($invoice['status'] ?? null) !== InvoiceStatus::DRAFT) {
                Flash::error(
                    'Upravit lze pouze návrh faktury.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoice/draft/' . $id . '/detail/#main'
                );
            }

            // Aktuální údaje dodavatele.
            $supplier = (new CompanyModel())
                ->billingData($companyId);

            if ($supplier === null) {
                Flash::error(
                    'Údaje dodavatele nebyly nalezeny.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoice/draft/' . $id . '/detail/#main'
                );
            }

            // Data ve stejném formátu jako při vytváření faktury.

            $invoiceData = $invoice['invoice'] ?? [];

            $invoiceData['contact_id'] = $invoice['contact_id'] ?? null;

            $this->view->data = [
                'invoice'   => $invoiceData,
                'supplier'  => $supplier,
                'customer'  => $invoice['customer'] ?? [],
                'workOrder' => $invoice['workOrder'] ?? [],
                'tasks'     => $invoice['tasks'] ?? [],
                'lines'     => $invoice['lines'] ?? [],
                'is_edit'   => true,
                'invoice_id' => $id,
            ];

            $this->view->contacts = (new ContactsModel())->all();

            return $this->render('invoices/create-from-task');

        } catch (RuntimeException $e) {

            LoggerHolder::get()->warning(
                'InvoiceController.edit rejected',
                [
                    'invoice_id' => $id,
                    'message'    => $e->getMessage(),
                ]
            );

            Flash::error($e->getMessage());

            Url::redirect(
                '/{tenant}/billing/invoice/#main'
            );

        } catch (Throwable $e) {

            LoggerHolder::get()->error(
                'InvoiceController.edit FAILED',
                [
                    'invoice_id' => $id,
                    'message'    => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ]
            );

            Flash::error(
                'Návrh faktury se nepodařilo načíst.'
            );

            Url::redirect(
                '/{tenant}/billing/invoice/#main'
            );
        }
    }

    
    /**
     * Vydá fakturu (POST).
     *
     * POST '/{tenant}/billing/invoice/draft/{id:\d+}/issue
     */
    public function issue(int $id): string
    {
        if ($_POST === []) {
            Flash::error('Neplatná žádost.');
            Url::back();
        }

        $this->checkCsrf();
        if($this->hasErrors()) {
            Flash::error('Neplatná žádost. ');
            Url::back();
        }

        try {
            $companyId = Auth::companyId();

            if ($companyId === null || $companyId <= 0) {
                Flash::error(
                    'Nelze určit pracovní prostor.'
                );

                Url::redirect(
                    '/{tenant}/billing/invoice/#main'
                );
            }

            $this->invoice->issue($companyId, $id);

            Flash::success('Faktura byla vydána.');

            Url::redirect(
                '/{tenant}/billing/invoice/issued/' . $id . '/detail/#main'
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
                '/{tenant}/billing/invoice/draft/' . $id . '/detail/#main'
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
                '/{tenant}/billing/invoice/draft/' . $id . '/detail/#main'
            );
        }
    }


}