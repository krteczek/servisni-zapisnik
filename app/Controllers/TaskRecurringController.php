<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Url;
use App\Core\Controller;
use App\Models\TaskModel;
use App\Models\RecurringTaskModel;


final class TaskRecurringController extends Controller
{
    public function show(int $taskId): string
    {
    	  $post = [];
        $taskModel = new TaskModel();
        $task = $taskModel->findById($taskId);

        if (!$task) {
        	   Flash::error('/kol neexistujke.');
            Url::redirect('/{tenant}/tasks/#main');
        }

        // TODO: načíst recurring pokud existuje
        $this->view->task = $task;
        $this->view->post = $post;


        return $this->render('tasks/recurring');
    }

    public function store(int $taskId): void
    {
        $taskModel = new TaskModel();
        $task = $taskModel->findById($taskId);

        if (!$task) {
        	   Flash::error('/kol neexistuje.');
            Url::redirect('/{tenant}/tasks/#main');
        }

        $data = [
            'task_id'            => $taskId,
            'company_id'         => Auth::companyId(),
            'created_by_user_id' => Auth::id(),
            'frequency'          => $_POST['frequency'],
            'interval'           => (int)$_POST['interval'],
            'next_run_at'        => $_POST['next_run_at'],
            'active'             => isset($_POST['active']) ? 1 : 0,
        ];

        $model = new RecurringTaskModel();
        $model->create($data);

        Url::redirect("/tasks/$taskId");
    }
}