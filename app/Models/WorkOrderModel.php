<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Auth;

class WorkOrderModel extends BaseModel
{
    protected string $table = 'work_orders';
    protected string $connection = 'work';

    /* ==========================================================
     * TRANSACTIONS
     * ========================================================== */

    /**
     * Zahájí databázovou transakci.
     * Používá se pro hromadné operace s tokeny (např. invalidace + vytvoření).
     *
     * Vedlejší efekty:
     * - Nastaví DB připojení do transakčního režimu
     *
     * TODO: [MAINTENANCE] Přesunout transakční metody do BaseModel
     *
     * @return void
     */
    public function begin(): void
    {
        if (!$this->db()->inTransaction()) {
            $this->db()->beginTransaction();
        }
    }

    /**
     * Potvrdí probíhající transakci.
     *
     * @return void
     */
    public function commit(): void
    {
        if ($this->db()->inTransaction()) {
            $this->db()->commit();
        }
    }

    /**
     * Zruší probíhající transakci.
     *
     * @return void
     */
    public function rollback(): void
    {
        if ($this->db()->inTransaction()) {
            $this->db()->rollBack();
        }
    }

public function forIndex(): array
{
    $companyId = Auth::companyId();
    $userId    = Auth::id();
    $role      = Auth::role();

    $sql = "
        SELECT
            w.*,
				-- 🔹 zákazník
				    c.company_name AS customer_name,
				    c.city         AS customer_city,
				    c.street       AS customer_street,
            -- TASKY
            COALESCE(t.tasks_total, 0)       AS tasks_total,
            COALESCE(t.tasks_open, 0)        AS tasks_open,
            COALESCE(t.tasks_done, 0)        AS tasks_done,
            COALESCE(t.tasks_cancelled, 0)   AS tasks_cancelled,

            -- REPORTY
            COALESCE(r.reports_count, 0)     AS reports_count,
            COALESCE(r.total_km, 0)          AS total_km,
            COALESCE(r.total_minutes, 0)     AS total_minutes

        FROM work_orders w
        -- napojení zákazníka
			LEFT JOIN contacts c
			    ON c.id = w.contact_id
			   AND c.company_id = :company_id_contacts
			   
        -- 🔹 agregace tasků
        LEFT JOIN (
            SELECT
                work_order_id,
                COUNT(*) AS tasks_total,
                SUM(status = 'open') AS tasks_open,
                SUM(status = 'done') AS tasks_done,
                SUM(status = 'cancelled') AS tasks_cancelled
            FROM tasks
            WHERE company_id = :company_id_tasks
            GROUP BY work_order_id
        ) t ON t.work_order_id = w.id
        -- 🔹 agregace reportů + minut + km
        LEFT JOIN (
            SELECT
                ta.work_order_id,
                COUNT(*) AS reports_count,
                SUM(ta.kilometers) AS total_km,
                SUM(
                    ta.minutes_spent +
                    COALESCE((
                        SELECT SUM(tap.minutes_spent)
                        FROM task_assignment_participants tap
                        WHERE tap.assignment_id = ta.id
                    ), 0)
                ) AS total_minutes
            FROM task_assignments ta
            WHERE ta.company_id = :company_id_reports
            GROUP BY ta.work_order_id
        ) r ON r.work_order_id = w.id

        WHERE w.company_id = :company_id_main
    ";

$params = [
    'company_id_tasks'     => $companyId,
    'company_id_reports'   => $companyId,
    'company_id_main'      => $companyId,
    'company_id_contacts'  => $companyId,
];
    if (in_array($role, ['admin', 'mistr'], true)) {
        $sql .= "
            ORDER BY 
                (w.created_by_user_id = :user_id) DESC,
                w.id DESC
        ";
        $params['user_id'] = $userId;
    } else {
        $sql .= " ORDER BY w.id DESC";
    }

    $orders = $this->fetchAll($sql, $params);

    foreach ($orders as &$order) {

        $order['total_minutes'] = (int)$order['total_minutes'];
        $order['total_hours_formatted'] =
            floor($order['total_minutes'] / 60) . 'h ' .
            ($order['total_minutes'] % 60) . 'm';

        $order['progress'] =
            $order['tasks_total'] > 0
                ? round(($order['tasks_done'] / $order['tasks_total']) * 100)
                : 0;
        $order['customer_name'] = $order['customer_name'] ?? '';

        $order['customer_address'] = trim(
                                        ($order['customer_street'] ?? '') . ' ' .
                                        ($order['customer_city'] ?? '')
                                       );
    }

    return $orders;
}
		//přepínání mezi stavy zakázky
public function recomputeStatus(int $orderId): void
{
    $order = $this->find($orderId);

    if (!$order) {
        return;
    }

    // Nechceme přepisovat ručně zrušenou zakázku
    if ($order['status'] === 'cancelled') {
        return;
    }

    $taskModel = new TaskModel();
    $stats = $taskModel->statsForWorkOrder($orderId);

    // 1️⃣ žádné tasky
    if ($stats['total'] === 0) {
        $this->update($orderId, ['status' => 'new']);
        return;
    }

    // 2️⃣ všechny cancelled
    if ($stats['cancelled'] === $stats['total']) {
        $this->update($orderId, [
            'status'    => 'cancelled',
            'closed_at' => date('Y-m-d H:i:s'),
        ]);
        return;
    }

    // 3️⃣ všechny done
    if ($stats['done'] === $stats['total']) {
        $this->update($orderId, [
            'status'    => 'done',
            'closed_at' => date('Y-m-d H:i:s'),
        ]);
        return;
    }

    // 4️⃣ jinak probíhá
    $this->update($orderId, [
        'status' => 'in_progress',
        'closed_at' => null,
    ]);
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
        ($order['status'] === 'new' || $order['status'] === 'in_progress')
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
        SELECT id, title, priority
        FROM {$this->tableName}
        WHERE id IN (" . implode(',', $placeholders) . ")
        AND company_id = :company_id
    ";
    
    $rows = $this->fetchAll($sql, $params); // Teď posíláme ID i company_id

    $result = [];

    foreach ($rows as $row) {
        $result[(int) $row['id']] = [
            'name' => $row['title'], // Opraveno - používáme 'title' místo 'name'
            'priority' => $row['priority']
        ];
    }

    return $result;
}

public function closeAsDone(int $orderId): bool
{
    $this->db()->beginTransaction();

    $stats = (new TaskModel())->statsForWorkOrder($orderId);

    if ($stats['open'] > 0 || $stats['done'] === 0) {
        $this->db()->rollBack();
        return false;
    }

    $ok = $this->update($orderId, [
        'status' => 'done',
        'closed_at' => date('Y-m-d H:i:s'),
    ]);

    if ($ok) {
        $this->db()->commit();
        return true;
    }

    $this->db()->rollBack();
    return false;
}


    public function createWithSequence(int $tenantId, array $data): int
    {
        $year = (int) date('Y');
        $number = (new WorkOrderSequencesModel())->next($year, $tenantId);

        $data['internal_number'] = $number;
        $data['year'] = $year;

        return $this->createWithTenant($tenantId, $data);
    }

}
