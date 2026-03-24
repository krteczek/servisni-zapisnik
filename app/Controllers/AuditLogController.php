<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLogModel;
use App\Core\Flash;
use App\Core\Url;

class AuditLogController extends Controller
{
    public function index(): string
    {
        $filters = [
            'user_id'    => $_GET['user_id']    ?? null,
            'action'     => $_GET['action']     ?? null,
            'entity'      => $_GET['entity']      ?? null,
            'from'       => $_GET['from']       ?? null,
            'to'         => $_GET['to']         ?? null,
            'ip'         => $_GET['ip']         ?? null,
            'user_agent' => $_GET['user_agent'] ?? null,
        ];

        $logs = (new AuditLogModel())->findByFilters($filters);

        $this->view->data    = $logs;
        $this->view->filters = $filters;
//var_dump($logs);
        return $this->render('admin/audit/index');
    }

public function detail(int $id): string
{
    $log = (new AuditLogModel())->find($id);

    if (!$log) {
        Flash::error('Audit záznam nebyl nalezen');
        Url::redirect('/admin/audit');
    }

    $this->view->logs = $log;

    return $this->render('admin/audit/detail');
}
}
