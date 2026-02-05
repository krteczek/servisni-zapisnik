<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class WorkOrderModel extends TenantModel
{
    protected string $table = 'work_orders';
    protected string $connection = 'work';

		//přepínání mezi stavy zakázky
public function recomputeStatus(int $orderId): void
{
    $taskModel = new TaskModel();
    $stats = $taskModel->statsForWorkOrder($orderId);

    if ($stats['total'] === 0) {
        $this->update($orderId, ['status' => 'new']);
        return;
    }

    if ($stats['open'] > 0) {
        $this->update($orderId, ['status' => 'in_progress']);
        return;
    }

    if ($stats['done'] > 0) {
        $this->update($orderId, ['status' => 'done']);
        return;
    }

    // zbývá jen cancelled
    $this->update($orderId, ['status' => 'cancelled']);
}

public function isClosed(array $order): bool
{
    return in_array($order['status'], ['done', 'cancelled', 'exported'], true);
}

public function canAddTask(array $order): bool
{
    return !$this->isClosed($order);
}

public function canBeCancelled(array $order, array $taskStats): bool
{
    return
        $order['status'] === 'new' || $order['status'] === 'in_progress'
        && $taskStats['open'] === 0
        && $taskStats['done'] === 0;
}

public function canBeDone(array $order, array $taskStats): bool
{
    return
        ($order['status'] === 'new' || $order['status'] === 'in_progress')
        && $taskStats['open'] === 0
        && $taskStats['done'] > 0;
}

}
