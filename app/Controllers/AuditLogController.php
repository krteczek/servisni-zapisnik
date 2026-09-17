<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLogModel;
use App\Core\Flash;
use App\Core\Url;

class AuditLogController extends Controller
{
    /**
     * Zobrazí seznam auditních záznamů podle zadaných filtrů.
     *
     * Filtry jsou načítány z GET parametrů:
     * user_id, action, entity, from, to, ip, user_agent.
     *
     * @return string Vyrenderovaná stránka se seznamem auditních záznamů.
     */
    public function index(): string
    {
        $filters = [
            'user_id'    => $_GET['user_id']    ?? null,
            'action'     => $_GET['action']     ?? null,
            'entity'     => $_GET['entity']     ?? null,
            'from'       => $_GET['from']       ?? null,
            'to'         => $_GET['to']         ?? null,
            'ip'         => $_GET['ip']         ?? null,
            'user_agent' => $_GET['user_agent'] ?? null,
        ];

        $logs = (new AuditLogModel())->findByFilters($filters);

        $this->view->logs    = $logs;
        $this->view->filters = $filters;

        return $this->render('admin/audit/index');
    }

    /**
     * Zobrazí detail auditního záznamu.
     *
     * @param int $id ID auditního záznamu.
     *
     * @return string Vyrenderovaná stránka s detailem auditního záznamu.
     */
    public function detail(int $id): string
    {
        $log = (new AuditLogModel())->find($id);

        if ($log === null) {
            Flash::error('Audit záznam nebyl nalezen');
            Url::redirect('/admin/audit/#main');
        }

        $this->view->log = $log;

        return $this->render('admin/audit/detail');
    }
}