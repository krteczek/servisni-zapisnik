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
public function createFromTask(int $id): string
{
    $this->setSessionCheck('task_id', $id);
    try {
        $this->view->data = $this->invoice->buildDraftFromTask($id);
        $this->view->contacts = (new ContactsModel())->all();
        dc($this->view->data);
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
        if($this->hasErrors() === true)
        {
            Flash::error($this->getError('_csrf'));
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
                $_POST
            );

            if ($result['success'] === false) {
                $this->setSessionCheck('task_id', $id);

                $this->view->errors = $result['errors'];
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

    /**
     * @param array<string, mixed> $post
     * @return array<string, string>
     */
    private function validateInvoiceData(array $post): array
    {
        $errors = [];

        $title = trim((string) ($post['title'] ?? ''));

        if ($title === '') {
            $errors['title'] = 'Zadejte název faktury.';
        }

        $issuedAt = trim((string) ($post['issued_at'] ?? ''));
        $dueDate = trim((string) ($post['due_date'] ?? ''));

        if (!$this->isValidDate($issuedAt)) {
            $errors['issued_at'] = 'Datum vystavení není platné.';
        }

        if (!$this->isValidDate($dueDate)) {
            $errors['due_date'] = 'Datum splatnosti není platné.';
        }

        if (
            $this->isValidDate($issuedAt)
            && $this->isValidDate($dueDate)
            && $dueDate < $issuedAt
        ) {
            $errors['due_date'] = 'Datum splatnosti nemůže být před datem vystavení.';
        }

        $lines = $post['lines'] ?? [];

        if (!is_array($lines)) {
            $errors['lines'] = 'Fakturační položky nejsou platné.';
            return $errors;
        }

        foreach ($lines as $index => $line) {
            if (!is_array($line)) {
                $errors["lines.{$index}"] = 'Fakturační položka není platná.';
                continue;
            }

            $description = trim((string) ($line['description'] ?? ''));

            if ($description === '') {
                $errors["lines.{$index}.description"] =
                    'Zadejte popis fakturační položky.';
            }

            $unit = trim((string) ($line['unit'] ?? ''));

            if ($unit === '') {
                $errors["lines.{$index}.unit"] =
                    'Vyberte jednotku množství.';
            }

            $priceUnit = trim((string) ($line['price_unit'] ?? ''));

            if ($priceUnit === '') {
                $errors["lines.{$index}.price_unit"] =
                    'Vyberte jednotku ceny.';
            }

            $quantityRaw = $line['quantity'] ?? null;

            if (
                $quantityRaw === null
                || $quantityRaw === ''
                || !is_numeric($quantityRaw)
            ) {
                $errors["lines.{$index}.quantity"] =
                    'Zadejte množství.';
            } elseif ((float) $quantityRaw <= 0) {
                $errors["lines.{$index}.quantity"] =
                    'Množství musí být větší než nula.';
            }

            $unitPriceRaw = $line['unit_price'] ?? null;

            if (
                $unitPriceRaw === null
                || $unitPriceRaw === ''
                || !is_numeric($unitPriceRaw)
            ) {
                $errors["lines.{$index}.unit_price"] =
                    'Zadejte cenu za jednotku.';
            } elseif ((float) $unitPriceRaw < 0) {
                $errors["lines.{$index}.unit_price"] =
                    'Cena nemůže být záporná.';
            }

            /*
            * Celkovou cenu z POST vůbec nepřebíráme.
            */
        }

        return $errors;
    }
}