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
        $data = $this->model->forIndex();
        [$data['myReadyToDoneOrders'], $data['myReadyToCancelOrders'] ] = $this->canOrdersBeDoneOrCancel($data['myOrdersInProgress']);
        $this->view->data = $data;
        return $this->render('workbench/index');

    }

    private function canOrdersBeDoneOrCancel(array $orders): array
    {
        $woCancel = [];
        $woDone = [];

        $orderIds = array_column($orders, 'id');

        $tasks = $this->model->getTasksForOrders($orderIds);

        $tasksByOrder = [];

        foreach ($tasks as $task) {
            $tasksByOrder[$task['work_order_id']][] = $task;
            //$orders['']
        }

        foreach ($orders as &$order) {

            $orderTasks = $tasksByOrder[$order['id']] ?? [];

            $order['tasks'] = $orderTasks;

            $doneCount = 0;
            $cancelCount = 0;
            $openCount = 0;

            foreach ($orderTasks as $task) {

                switch ($task['status']) {

                    case 'done':
                        $doneCount++;
                        break;

                    case 'cancelled':
                        $cancelCount++;
                        break;

                    default:
                        $openCount++;
                        break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Zakázka lze dokončit
            |--------------------------------------------------------------------------
            */

            $order['can_be_done'] =
                count($orderTasks) > 0
                && ($doneCount + $cancelCount) === count($orderTasks);
            
            
            /*
            |--------------------------------------------------------------------------
            | Zakázka lze stornovat
            |--------------------------------------------------------------------------
            */

            $order['can_be_cancelled'] =
                $doneCount === 0
                && (
                    count($orderTasks) === 0
                    || $cancelCount === count($orderTasks)
                );

            if ($order['can_be_done']) {
                $woDone[] = $order;
            }

            if ($order['can_be_cancelled']) {
                $woCancel[] = $order;
            }

            unset($order);
        }
            return [$woDone, $woCancel];
    }
}
