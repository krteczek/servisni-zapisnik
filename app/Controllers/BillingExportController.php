<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\ViewContext;
use App\Core\Url;
use App\Core\TenantContext;
use App\Services\Billing\BillingExportService;
use Throwable;
//use App\Core\ViewContext;

final class BillingExportController extends Controller
{
    private BillingExportService $service;


    public function __construct(ViewContext $view)
    {
    	  parent::__construct($view); // 🔥 KLÍČOVÉ
        $this->service = new BillingExportService();
    }

    /**
     * GET /exports/billing
     */
    public function index(): string
    {
        $companyId = Auth::companyId();

        $exports = $this->service->getExports($companyId);
        if(!$exports)
        {
        	  $exports = [];
        }
        $this->view->data = $exports;

        return $this->render('exports/billing/index');
    }

    /**
     * GET /exports/billing/create
     */
    public function create(): string
    {
        return $this->render('/exports/billing/create');
    }

    /**
     * POST /exports/billing/create
     */
    public function store(): string
    {
        $companyId = Auth::companyId();
        $userId    = Auth::id();

        $from = Request::post('period_from');
        $to   = Request::post('period_to');
        $note = Request::post('note');

        try {
            $exportId = $this->service->createExport(
                $companyId,
                $userId,
                $from,
                $to,
                $note
            );

            Url::redirect("/{tenant}/exports/billing/{$exportId}/detail");

        } catch (Throwable $e) {
            // UX: žádné technické detaily
            Url::redirect('/{tenant}/exports/billing/create?error=1');
        }
    }

    /**
     * GET /exports/billing/{id}/detail
     */
    public function detail(int $id): string
    {
        $companyId = Auth::companyId();

        $export = $this->service->getExportDetail($companyId, $id);

        if (!$export) {
            Url::redirect('/{tenant}/exports/billing');
        }
        $this->view->data = $export;

        return $this->render('exports/billing/detail');
    }

    /**
     * GET /exports/billing/{id}/pdf
     */
    public function pdf(int $id): void
    {
        $companyId = Auth::companyId();

        try {
            $pdf = $this->service->generatePdf($companyId, $id);

            Response::pdf($pdf, "export-{$id}.pdf");

        } catch (Throwable $e) {
            Url::redirect("/{tenant}/exports/billing/{$id}?error=1");
        }
    }
}