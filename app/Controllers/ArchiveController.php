<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

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

class ArchiveController extends Controller
{
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
            // future:
            // 'date_from' => ...
        ];
    }
    public function tasks(): string
    {

        $filters = $this->getFiltersFromRequest();
        
        $data = (new TaskModel())->filterArchive($filters);

        $this->view->data = $data;
        $this->view->title .= ' (' . count($data) . ')';
        $this->view->filters = $filters;
        $this->view->type = 'tasks'; // 👈 důležité pro view

        return $this->render('archive/index');
    }

    public function workOrders(): string
    {
        $filters = $this->getFiltersFromRequest();
        
        $data = (new WorkOrderModel())->filterArchive($filters);

        $this->view->data = $data;
        $this->view->title .= ' (' . count($data) . ')';
        $this->view->filters = $filters;
        $this->view->type = 'work-orders'; // 👈 důležité pro view

        return $this->render('archive/index');

    }
}