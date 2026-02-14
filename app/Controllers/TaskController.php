<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\WorkOrderModel;
use App\Core\Url;

class TaskController extends Controller
{
public function index(): string
{
    $tasks = (new TaskModel())->forIndex();

    $teamIds = array_unique(
        array_filter(array_column($tasks, 'team_id'))
    );

    $teamColors = (new TeamModel())->getColorsByIds($teamIds);

    foreach ($tasks as &$task) {
        $task['team_color'] = $teamColors[$task['team_id']] ?? '#999';
    }
    
    //nutno přidat název zakázky, ke které tento úkol patří
		//$wo = new WorkOrderModel();
		//$order = $wo->getOrderOrRedirect($orderId)
    $this->view->tasks = $tasks;
    return $this->render('tasks/index');
}

    public function createForm(?int $orderId = null): string
    {
        $this->view->orderId = $orderId;
        return $this->render('tasks/create');
    }

    public function create(): string
    {/*
$order = $workOrderModel->find($orderId);

if (!$workOrderModel->canAddTask($order)) {
    $this->flashError('K uzavřené zakázce nelze přidat nový úkol.');
    return $this->redirect("/work-orders/{$orderId}#main");
}*/
        $this->checkCsrf();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->addError('title', 'Název úkolu je povinný');
        }

        if ($this->hasErrors()) {
            return $this->createForm();
        }

        (new TaskModel())->create([
            'title'              => $title,
            'description'        => $_POST['description'] ?? null,
            'team_id'            => (int) $_POST['team_id'],
            'work_order_id'      => $_POST['work_order_id'] ?: null,
            'created_by_user_id' => $this->view->user['id'],
        ]);

        Url::redirect('/dashboard');
        exit;
    }

    public function done(int $taskId): void
    {
        (new TaskModel())->markDone($taskId);
        Url::back();
    }

    public function cancel(int $taskId): void
    {
        (new TaskModel())->cancel($taskId);
        Url::back();
    }
}
