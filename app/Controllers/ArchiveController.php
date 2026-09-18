<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\WorkOrderModel;
use App\Models\TaskAssignmentModel;
use App\Models\RecurringTaskModel;
use App\Services\Tasks\TaskStatus;
use App\Services\WorkOrders\WOStatus;
use App\Core\Url;
use App\Core\Flash;
use App\Core\Auth;
use App\Core\Roles;
use App\Core\Config;
use App\Core\LoggerHolder;
use App\Core\Transaction;

use Throwable;

class ArchiveController extends Controller
{

    /**
     * @return array{status: string, q: string}
     */
    private function getFiltersFromRequest(): array
    {
        $status = $_GET['status'] ?? 'all';
        $q = trim($_GET['q'] ?? '');

        if (!in_array($status, ['all', 'done', 'cancelled'], true)) {
            $status = 'all';
        }

        return [
            'status' => $status,
            'q'      => $q,
        ];
    }


    public function tasks(): string
    {

        $filters = $this->getFiltersFromRequest();
        
        $tasks = (new TaskModel())->filterArchive($filters);

        $this->view->archiveTasks = $tasks;
        $this->view->title .= ' (' . count($tasks) . ')';
        $this->view->filters = $filters;
        //$this->view->type = 'tasks'; // zrušeno, výpis rozdělen na tasky a ordery, samostatné soubory
        //debugViewVariables($this->view);
        return $this->render('archive/indexTasks');
    }


    public function detailTaskAndReports(int $taskId): string
    {
        // získáme task
        $task = (new TaskModel())->find($taskId);
        if($task === null) {
            Flash::error('Požadovaný úkol nebyl nalezen. Nejspíše neexistuje.');
            Url::redirect('/{tenant}/archive/tasks/#main');
        }

        //získáme reporty
        $reports = [];
        $task['allReportsParticipants'] = [];

        $wo = (new WorkOrderModel())->find($task['work_order_id']);
        if ($wo === null) {
            Flash::error('Zakázka neexistuje.');
            Url::redirect('/{tenant}/archive/tasks/#main');
        }
        
        $task['WOStatus'] = $wo['status'];
        $task['totalKm'] = 0;
        $this->view->title .= ' > ' . $task['title'] . ' ';
        if(TaskStatus::isDone($task["status"])) {
            //získáme reporty podle id tasku
            $model = new TaskAssignmentModel();
            $reports = $model->findByTask($taskId);
            $task['allReportsParticipants'] = $model->getTaskParticipants($taskId);
            $task['totalKm'] = $model->getTaskTotalKilometers($taskId);
            
        }

         // přiřadíme proměnnym ve view hodnoty 
        $this->view->archiveTask = $task;
        $this->view->reports = $reports;


        return $this->render('archive/detailTask');
    }


    public function workOrders(): string
    {
        $filters = $this->getFiltersFromRequest();
        
        $data = (new WorkOrderModel())->filterArchive($filters);

        $this->view->archiveOrders = $data;
        $this->view->title .= ' (' . count($data) . ')';
        $this->view->filters = $filters;
        $this->view->type = 'work-orders'; // 👈 důležité pro view

        return $this->render('archive/indexOrders');

    }
}