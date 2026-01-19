<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditModel;

class AuditLogController extends Controller
{
    public function index(): string
    {
        $filters = [
            'user_id'    => $_GET['user_id']    ?? null,
            'action'     => $_GET['action']     ?? null,
            'table'      => $_GET['table']      ?? null,
            'from'       => $_GET['from']       ?? null,
            'to'         => $_GET['to']         ?? null,
            'ip'         => $_GET['ip']         ?? null,
            'user_agent' => $_GET['user_agent'] ?? null,
        ];

        $logs = (new AuditModel())->findByFilters($filters,);

        $this->view->logs    = $logs ?? [];
        $this->view->filters = $filters ?? [];

        return $this->render('admin/audit/index');
    }

    public function detail(int $id): string
    {
        $log = (new AuditModel())->findById($id);

        if (!$log) {
            $this->view->errors[] = 'Audit záznam nebyl nalezen';
            return $this->render('admin/audit/index');
        }

        $this->view->log = $log;

        return $this->render('admin/audit/detail');
    }
}
