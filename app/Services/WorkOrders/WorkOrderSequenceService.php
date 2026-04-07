<?php
declare(strict_types=1);

namespace App\Services\WorkOrders;
use App\Models\WorkOrderSequencesModel;

class WorkOrderSequenceService
{
    private WorkOrderSequencesModel $model;

    public function __construct(WorkOrderSequencesModel $model)
    {
        $this->model = $model;
    }

    public function nextNumber(): int
    {
        $year = (int) date('Y');
        return $this->model->next($year);
    }
}