<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\WorkbenchModel;
use App\Core\ViewContext;
/*
use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\WorkOrderModel;
use App\Models\AssignmentModel;
use App\Models\RecurringTaskModel;
use App\Core\Url;
use App\Core\Flash;
use App\Core\Auth;
use App\Core\Roles;
use App\Core\Config;
use App\Core\LoggerHolder;
use App\Core\Transaction;

use Throwable;
*/
class WorkbenchController extends Controller
{
    private WorkbenchModel $model;
    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->model = new WorkbenchModel();
    }
	public function index(): string
	{
        $this->view->data = $this->model->forIndex();
        return $this->render('workbench/index');

    }
}
