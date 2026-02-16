<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class WorkOrderModel extends BaseModel
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

    if ($stats['done'] > 0 && $stats['open'] === 0) {
        $this->update($orderId, [
            'status'    => 'done',
            'closed_at'=> date('Y-m-d H:i:s'),
        ]);
        return;
    }

    if ($stats['canceled'] === $stats['total']) {
        $this->update($orderId, [
            'status'    => 'cancelled',
            'closed_at'=> date('Y-m-d H:i:s'),
        ]);
        return;
    }

    $this->update($orderId, ['status' => 'in_progress']);
}


public function recomputeStatusOld(int $orderId): void
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


public function getNamesByIds(array $ids): array
{
    if (empty($ids)) {
        return [];
    }
    
    $companyId = parent::tenantId();
    $params = [];
    $placeholders = [];

    foreach ($ids as $i => $id) {
        $key = "id_$i";
        $placeholders[] = ":$key";
        $params[$key] = (int) $id; // Parametry pro ID
    }

    // Přidáme company_id do parametrů
    $params['company_id'] = $companyId;

    $sql = "
        SELECT id, title
        FROM {$this->tableName}
        WHERE id IN (" . implode(',', $placeholders) . ")
        AND company_id = :company_id
    ";
    
    $rows = $this->fetchAll($sql, $params); // Teď posíláme ID i company_id

    $result = [];

    foreach ($rows as $row) {
        $result[(int) $row['id']] = [
            'name' => $row['title'], // Opraveno - používáme 'title' místo 'name'
        ];
    }

    return $result;
}

}
