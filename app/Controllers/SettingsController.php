<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\LoggerHolder;
use App\Core\Url;
use App\Core\ViewContext;
use App\Models\SettingsModel;
use App\Services\Settings\BillingMode;
use App\Services\Invoice\InvoiceNumberService;
use Throwable;

class SettingsController extends Controller
{
    private SettingsModel $model;

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->model = new SettingsModel();
    }

    public function billing(): string
    {
        $this->view->data = [
            'billing' => $this->model->getBillingSettings(),
            'work_orders' => $this->model->getWorkOrderSettings(),
        ];

        return $this->render('settings/billing');
    }

    public function saveBillingMode(): void
    {
        $this->checkCsrf();

        $mode = $_POST['billing_mode'] ?? BillingMode::INTERNAL;

        if (!is_string($mode) || !BillingMode::isValid($mode)) {
            Flash::error('Neplatný režim fakturace.');
            Url::redirect('/{tenant}/system/settings/billing/#main');
        }

        try {
            $settings = $this->model->getBillingSettings();
            $settings['billing_mode'] = $mode;

            $this->model->updateBillingSettings($settings);

            Flash::success('Režim fakturace byl uložen.');
        } catch (Throwable $e) {
            LoggerHolder::get()->error(
                'SettingsController.saveBillingMode FAILED',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            Flash::error('Režim fakturace se nepodařilo uložit.');
        }

        Url::redirect('/{tenant}/system/settings/billing/#main');
    }

    public function saveInvoiceDueDays(): void
    {
        $this->checkCsrf();

        $value = $_POST['invoice_due_days'] ?? null;

        if (!is_string($value) || !ctype_digit($value)) {
            Flash::error('Splatnost faktury musí být celé číslo.');
            Url::redirect('/{tenant}/system/settings/billing/#main');
        }

        $dueDays = (int) $value;

        if ($dueDays < 1 || $dueDays > 365) {
            Flash::error('Splatnost faktury musí být v rozmezí 1 až 365 dnů.');
            Url::redirect('/{tenant}/system/settings/billing/#main');
        }

        try {
            $settings = $this->model->getBillingSettings();
            $settings['invoice_due_days'] = $dueDays;

            $this->model->updateBillingSettings($settings);

            Flash::success('Splatnost faktur byla uložena.');
        } catch (Throwable $e) {
            LoggerHolder::get()->error(
                'SettingsController.saveInvoiceDueDays FAILED',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            Flash::error('Splatnost faktur se nepodařilo uložit.');
        }

        Url::redirect('/{tenant}/system/settings/billing/#main');
    }

    public function saveInvoiceNumberStart(): void
    {
        $this->checkCsrf();

        $value = $_POST['invoice_number_start'] ?? null;

        if (!is_string($value) || !ctype_digit($value)) {
            Flash::error('Počáteční číslo faktur musí být celé číslo.');
            Url::redirect('/{tenant}/system/settings/billing/#main');
        }

        $number = (int) $value;

        if ($number < 1) {
            Flash::error('Počáteční číslo faktur musí být větší než 0.');
            Url::redirect('/{tenant}/system/settings/billing/#main');
        }

        try {
            $settings = $this->model->getBillingSettings();
            $settings['invoice_number_start'] = $number;

            $this->model->updateBillingSettings($settings);

            Flash::success('Počáteční číslo faktur bylo uloženo.');
        } catch (Throwable $e) {
            LoggerHolder::get()->error(
                'SettingsController.saveInvoiceNumberStart FAILED',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            Flash::error('Počáteční číslo faktur se nepodařilo uložit.');
        }

        Url::redirect('/{tenant}/system/settings/billing/#main');
    }

    public function confirmInvoiceNumberStart(): void
    {
        $this->checkCsrf();

        try {
            $this->model->confirm(
                'billing',
                'invoice_number_start'
            );

            Flash::success('Počáteční číslo faktur bylo potvrzeno.');
        } catch (Throwable $e) {
            LoggerHolder::get()->error(
                'SettingsController.confirmInvoiceNumberStart FAILED',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            Flash::error('Počáteční číslo faktur se nepodařilo potvrdit.');
        }

        Url::redirect('/{tenant}/system/settings/billing/#main');
    }

    public function saveInvoiceNumberFormat(): void
    {
        $this->checkCsrf();

        $format = $_POST['invoice_number_format'] ?? null;


        if (!is_string($format) || trim($format) === '') {
            Flash::error('Formát čísla faktury nesmí být prázdný.');
            Url::redirect('/{tenant}/system/settings/billing/#main');
        }

        $format = trim($format);

        $numberService = new InvoiceNumberService();

        if (!$numberService->isValidFormat($format)) {
            Flash::error('Neplatný formát čísla faktury.');
            Url::redirect('/{tenant}/system/settings/billing/#main');
        }

        try {
            $settings = $this->model->getBillingSettings();
            $settings['invoice_number_format'] = trim($format);

            $this->model->updateBillingSettings($settings);

            Flash::success('Formát čísla faktury byl uložen.');
        } catch (Throwable $e) {
            LoggerHolder::get()->error(
                'SettingsController.saveInvoiceNumberFormat FAILED',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            Flash::error('Formát čísla faktury se nepodařilo uložit.');
        }

        Url::redirect('/{tenant}/system/settings/billing/#main');
    }

    public function confirmInvoiceNumberFormat(): void
    {
        $this->checkCsrf();

        try {
            $this->model->confirm(
                'billing',
                'invoice_number_format'
            );

            Flash::success('Formát čísla faktury byl potvrzen.');
        } catch (Throwable $e) {
            LoggerHolder::get()->error(
                'SettingsController.confirmInvoiceNumberFormat FAILED',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            Flash::error('Formát čísla faktury se nepodařilo potvrdit.');
        }

        Url::redirect('/{tenant}/system/settings/billing/#main');
    }
}