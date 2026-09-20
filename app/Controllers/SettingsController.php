<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SettingsModel;
use App\Core\ViewContext;
use App\Services\Settings\BillingMode;
use App\Core\Flash;
use App\Core\Url;
use App\Services\Settings\SettingsService;
use Throwable;
use App\Core\LoggerHolder;


class SettingsController extends Controller
{
    private SettingsModel $model;
    

    public function __construct(ViewContext $view) 
    {
        parent::__construct($view);
        $this->model = new SettingsModel();
    }


    public function index(): string
    {
        $this->view->data = [
            'billing' => $this->model->getBillingSettings(),
            'work_orders' => $this->model->getWorkOrderSettings(),
        ];

        return $this->render('settings/index');
    }

    public function saveBilling(): void
    {
        $this->checkCsrf();

        $mode = $_POST['billing_mode'] ?? BillingMode::INTERNAL;

        if (!BillingMode::isValid($mode)) {
            Flash::error('Neplatný režim fakturace.');
            Url::redirect('/{tenant}/system/settings#settings-billing/#main');
        }

        $default = (new SettingsService())->getInvoiceDueDays();
        $dueDays = (int)($_POST['invoice_due_days'] ?? $default);
        if ($dueDays < 1 || $dueDays >365) {
            $dueDays = $default;
        }
        try {
            
            $this->model->updateBillingSettings([
                'billing_mode' => $mode,
                'invoice_due_days' => $dueDays,
            ]);

            Flash::success('Nastavení fakturace bylo uloženo.');
            Url::redirect('/{tenant}/system/settings#settings-billing/#main');

        } catch (Throwable $e) {
            LoggerHolder::get()->error('SettingsController.saveBilling FAILED', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
        ]);

            Flash::error('Nastavení fakturace se nepodařilo uložit.');
            Url::redirect('/{tenant}/system/settings#settings-billing/#main');

        }
     }
}