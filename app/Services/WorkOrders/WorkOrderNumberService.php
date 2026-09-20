<?php
declare(strict_types=1);

namespace App\Services\WorkOrders;

use App\Models\WorkOrderModel;
use App\Core\Auth;
use App\Models\WorkOrderSequencesModel;
use App\Services\WorkOrders\WorkOrderSequenceService;
use App\Core\Database;
use Throwable;
use PDO;
use App\Core\Types;

/**
 * @phpstan-import-type WorkOrderCreateData from Types
 */
class WorkOrderNumberService
{
    /** 
     * @param WorkOrderCreateData $data
     */
    public function generateAndCreate(array $data, PDO $pdo): int
    {
        $workOrderModel = new WorkOrderModel();
        $sequenceModel = new WorkOrderSequencesModel();

        $workOrderModel->setConnection($pdo);
        $sequenceModel->setConnection($pdo);

        $sequenceService = new WorkOrderSequenceService($sequenceModel);

        $number = $sequenceService->nextNumber();

        $data['internal_number'] = $number;
        $data['year'] = (int) date('Y');

        return $workOrderModel->create($data);
    }

}