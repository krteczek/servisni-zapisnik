<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\WorkbenchModel;
use App\Core\ViewContext;
use App\Services\Settings\SettingsService;

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
        [$data['otherReadyToDoneOrders'], $data['otherReadyToCancelOrders'] ] = $this->canOrdersBeDoneOrCancel($data['otherOrdersInProgress']);
      
        
        $this->view->data = $data;
        $this->view->data['isInternalBilling'] = (new SettingsService())->isInternalBilling();
        $this->view->data['isExternalAccounting'] = (new SettingsService())->isExternalAccounting();
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
