<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SettingsModel;
use App\Core\ViewContext;
use App\Services\Settings\BillingMode;
use App\Core\Flash;
use App\Core\Url;

/*
use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\WorkOrderModel;
use App\Models\AssignmentModel;
use App\Models\RecurringTaskModel;


use App\Core\Auth;
use App\Core\Roles;
use App\Core\Config;
use App\Core\LoggerHolder;
use App\Core\Transaction;

use Throwable;
*/
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

        $allowed = BillingMode::all();

        $mode = $_POST['billing_mode'] ?? BillingMode::INTERNAL;

        if (!BillingMode::isValid($mode)) {
            Flash::error('Neplatný režim fakturace.');
            Url::redirect('/{tenant}/system/settings#settings-billing');
        }

        $settings = new SettingsModel();

        $settings->updateBillingSettings([
            'billing_mode' => $mode,
        ]);

        Flash::success('Nastavení bylo uloženo.');

        Url::redirect('/{tenant}/system/settings#settings-billing');
    }
}