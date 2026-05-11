<?php
declare(strict_types=1);

namespace App\Services\Billing;

use App\Core\Database;
use \Throwable;
//use App\Core\Auth;
use PDO;

final class BillingExportService
{
	 
    protected string $connection = 'work';

    public function __construct()
    {
        //zrušeno protože se přešlo na model jedné databáze pro uživatele i práci.
        //Database::useWorkDatabase('work');
    }

    public function getExports(int $companyId): array
    {

        $pdo = Database::work();
        $stmt = $pdo->prepare("
            SELECT *
            FROM billing_exports
            WHERE company_id = :company_id
            ORDER BY exported_at DESC
        ");

        $stmt->execute([
            'company_id' => $companyId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function createExport(
    int $companyId,
    int $userId,
    string $from,
    string $to,
    ?string $note
): int {

	if (strtotime($from) > strtotime($to)) {
	    throw new \InvalidArgumentException('Invalid period');
	}

    $pdo = Database::work();

    $pdo->beginTransaction();
    try {
        // 1) vytvoř export
        $stmt = $pdo->prepare("
            INSERT INTO billing_exports (
                company_id,
                exported_by_user_id,
                period_from,
                period_to,
                note
            ) VALUES (
                :company_id,
                :user_id,
                :from,
                :to,
                :note
            )
        ");

        $stmt->execute([
            'company_id' => $companyId,
            'user_id'    => $userId,
            'from'       => $from,
            'to'         => $to,
            'note'       => $note,
        ]);

        $exportId = (int) $pdo->lastInsertId();

        // 2) načti data pro export
        $items = $this->getExportableItems($companyId, $from, $to);

        if (!$items) {
            throw new \RuntimeException('No data to export');
        }

        // 3) ulož snapshot
        $this->insertItems($exportId, $companyId, $items);

        // 4) označ jako exportované
        $this->markAsExported($exportId, $companyId, $from, $to, $items);

        $pdo->commit();

        return $exportId;

    } catch (\Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
public function getExportDetail(int $companyId, int $id): ?array
{
    $export = $this->get($id, $companyId);

    if (!$export) {
        return null;
    }

    $pdo = Database::work();

    $stmt = $pdo->prepare("
        SELECT *
        FROM billing_export_items
        WHERE billing_export_id = :id
          AND company_id = :company_id
    ");

    $stmt->execute([
        'id' => $id,
        'company_id' => $companyId
    ]);

    $export['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $export;
}
public function generatePdf(int $companyId, int $id): string
{
    $export = $this->getExportDetail($companyId, $id);

    if (!$export) {
        throw new \RuntimeException('Export not found');
    }

    return $this->renderHtml($export, $export['items']);
}
    // =========================
    // PRIVATE HELPERS
    // =========================

    private function get(int $id, int $companyId): ?array
    {
        $pdo = Database::work();

        $stmt = $pdo->prepare("
            SELECT *
            FROM billing_exports
            WHERE id = :id
              AND company_id = :company_id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
            'company_id' => $companyId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    private function renderHtml(array $export, array $items): string
    {
        ob_start();
        ?>

        <h1>Fakturační podklady</h1>

        <p>
            Období: <?= e($export['period_from']) ?>
            -
            <?= e($export['period_to']) ?>
        </p>

        <table border="1" cellpadding="5" cellspacing="0">

<tr>
    <th>Úkol</th>
    <th>Název</th>
    <th>Uživatel</th>
    <th>Minuty</th>
    <th>Km</th>
</tr>

<?php foreach ($items as $item): ?>
<tr>
    <td><?= $item['task_id'] ?></td>
    <td><?= e($item['task_title']) ?></td>
    <td><?= tx($item['user_name']) ?></td>
    <td><?= $item['minutes_spent'] ?></td>
    <td><?= $item['kilometers'] ?></td>
</tr>
<?php endforeach; ?>
        </table>

        <?php
        return ob_get_clean();
    }

public function getExportableItems(int $companyId, string $from, string $to): array
{
    $pdo = Database::work();

    $stmt = $pdo->prepare("
        SELECT
            ta.id AS task_assignment_id,
            ta.task_id,
            ta.work_order_id,
            t.title AS task_title,
            wo.title AS work_order_title,
            tap.user_id,
            CONCAT(u.first_name, ' ', u.last_name) AS user_name,
            tap.minutes_spent,
            CASE
               WHEN tap.user_id = ta.user_id THEN ta.kilometers
               ELSE 0
               END AS kilometers,
            tap.created_at

        FROM task_assignment_participants tap

        JOIN task_assignments ta
            ON ta.id = tap.assignment_id
           AND ta.company_id = tap.company_id

        JOIN tasks t
            ON t.id = ta.task_id
           AND t.company_id = ta.company_id

        LEFT JOIN work_orders wo
            ON wo.id = ta.work_order_id
           AND wo.company_id = ta.company_id

        JOIN users u
            ON u.id = tap.user_id

        WHERE tap.company_id = :company_id
            AND tap.created_at BETWEEN :from AND :to
            AND tap.billing_export_id IS NULL
            AND ta.billing_export_id IS NULL
            AND t.billing_export_id IS NULL
            AND t.status = 'done'
        FOR UPDATE
    ");

    $stmt->execute([
        'company_id' => $companyId,
        'from' => $from . ' 00:00:00',
        'to'   => $to . ' 23:59:59',
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

private function insertItems(int $exportId, int $companyId, array $items): void
{
    $pdo = Database::work();

    $stmt = $pdo->prepare("
        INSERT INTO billing_export_items (
            company_id,
            billing_export_id,
            task_id,
            task_assignment_id,
            user_id,
            minutes_spent,
            kilometers,
            work_order_id,
            work_order_title,
            task_title,
            user_name
        ) VALUES (
            :company_id,
            :export_id,
            :task_id,
            :assignment_id,
            :user_id,
            :minutes,
            :km,
            :wo_id,
            :wo_title,
            :task_title,
            :user_name
        )
    ");

    foreach ($items as $item) {
        $stmt->execute([
            'company_id'   => $companyId,
            'export_id'    => $exportId,
            'task_id'      => $item['task_id'],
            'assignment_id'=> $item['task_assignment_id'],
            'user_id'      => $item['user_id'],
            'minutes'      => $item['minutes_spent'],
            'km'           => $item['kilometers'],
            'wo_id'        => $item['work_order_id'],
            'wo_title'     => $item['work_order_title'],
            'task_title'   => $item['task_title'],
            'user_name'    => $item['user_name'],
        ]);
    }
}


private function markAsExported(
    int $exportId,
    int $companyId,
    string $from,
    string $to,
    array $items
): void {
    $pdo = Database::work();

    // =========================
    // 0) TASKS
    // =========================
    $taskIds = array_unique(array_column($items, 'task_id'));

    if ($taskIds) {

        $in = implode(',', array_fill(0, count($taskIds), '?'));

        $stmt = $pdo->prepare("
            UPDATE tasks
            SET billing_export_id = ?
            WHERE company_id = ?
            AND status = 'done'
            AND billing_export_id IS NULL
            AND id IN ($in)
        ");

        $stmt->execute(array_merge(
            [$exportId, $companyId],
            $taskIds
        ));
    }

    // =========================
    // 1) ASSIGNMENTS
    // =========================
    $assignmentIds = array_unique(array_column($items, 'task_assignment_id'));

    if ($assignmentIds) {
        $in = implode(',', array_fill(0, count($assignmentIds), '?'));

        $stmt = $pdo->prepare("
            UPDATE task_assignments
            SET billing_export_id = ?
            WHERE company_id = ?
              AND id IN ($in)
        ");

        $stmt->execute(array_merge([$exportId, $companyId], $assignmentIds));
    }

    // =========================
    // 2) PARTICIPANTS (KLÍČOVÉ)
    // =========================
    $participantKeys = [];

    foreach ($items as $item) {
        // unikátní kombinace assignment + user
        $participantKeys[] = [
            'assignment_id' => $item['task_assignment_id'],
            'user_id'       => $item['user_id'],
        ];
    }

    if (!$participantKeys) {
        return;
    }

    $conditions = [];
    $params = [
        'export_id'  => $exportId,
        'company_id' => $companyId,
    ];
    $participantKeys = array_unique($participantKeys, SORT_REGULAR);
    foreach ($participantKeys as $i => $key) {
        $conditions[] = "(assignment_id = :a{$i} AND user_id = :u{$i})";
        $params["a{$i}"] = $key['assignment_id'];
        $params["u{$i}"] = $key['user_id'];
    }

    $sql = "
        UPDATE task_assignment_participants
        SET billing_export_id = :export_id
        WHERE company_id = :company_id
          AND billing_export_id IS NULL
          AND (" . implode(' OR ', $conditions) . ")
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}



public function cancelExport(
    int $companyId,
    int $exportId,
    int $userId,
    ?string $reason = null
): void {

    $pdo = Database::work();

    $pdo->beginTransaction();

    try {

        // =========================
        // 1) LOCK EXPORT
        // =========================
        $stmt = $pdo->prepare("
            SELECT *
            FROM billing_exports
            WHERE id = :id
              AND company_id = :company_id
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([
            'id' => $exportId,
            'company_id' => $companyId,
        ]);

        $export = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$export) {
            throw new \RuntimeException('Export not found');
        }

        if ($export['status'] === 'cancelled') {
            throw new \LogicException('Export already cancelled');
        }

        // =========================
        // 2) LOAD SNAPSHOT ITEMS
        // =========================
        $stmt = $pdo->prepare("
            SELECT
                task_id,
                task_assignment_id,
                user_id
            FROM billing_export_items
            WHERE billing_export_id = :export_id
              AND company_id = :company_id
            FOR UPDATE
        ");

        $stmt->execute([
            'export_id' => $exportId,
            'company_id' => $companyId,
        ]);

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // =========================
        // 3) CANCEL EXPORT
        // =========================
        $stmt = $pdo->prepare("
            UPDATE billing_exports
            SET
                status = 'cancelled',
                cancelled_at = NOW(),
                cancelled_by_user_id = :user_id,
                cancel_reason = :reason
            WHERE id = :id
              AND company_id = :company_id
        ");

        $stmt->execute([
            'id' => $exportId,
            'company_id' => $companyId,
            'user_id' => $userId,
            'reason' => $reason,
        ]);

        // =========================
        // 4) RETURN TASKS
        // =========================
        $taskIds = array_unique(array_column($items, 'task_id'));

        if ($taskIds) {

            $in = implode(',', array_fill(0, count($taskIds), '?'));

            $stmt = $pdo->prepare("
                UPDATE tasks
                SET billing_export_id = NULL
                WHERE company_id = ?
                  AND billing_export_id = ?
                  AND id IN ($in)
            ");

            $stmt->execute(array_merge(
                [$companyId, $exportId],
                $taskIds
            ));
        }

        // =========================
        // 5) RETURN ASSIGNMENTS
        // =========================
        $assignmentIds = array_unique(
            array_column($items, 'task_assignment_id')
        );

        if ($assignmentIds) {

            $in = implode(',', array_fill(0, count($assignmentIds), '?'));

            $stmt = $pdo->prepare("
                UPDATE task_assignments
                SET billing_export_id = NULL
                WHERE company_id = ?
                  AND billing_export_id = ?
                  AND id IN ($in)
            ");

            $stmt->execute(array_merge(
                [$companyId, $exportId],
                $assignmentIds
            ));
        }

        // =========================
        // 6) RETURN PARTICIPANTS
        // =========================
        if ($items) {

            $conditions = [];
            $params = [
                'company_id' => $companyId,
                'export_id'  => $exportId,
            ];

            foreach ($items as $i => $item) {

                $conditions[] =
                    "(assignment_id = :a{$i} AND user_id = :u{$i})";

                $params["a{$i}"] =
                    $item['task_assignment_id'];

                $params["u{$i}"] =
                    $item['user_id'];
            }

            $sql = "
                UPDATE task_assignment_participants
                SET billing_export_id = NULL
                WHERE company_id = :company_id
                  AND billing_export_id = :export_id
                  AND (
                      " . implode(' OR ', $conditions) . "
                  )
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        $pdo->commit();

    } catch (\Throwable $e) {

        $pdo->rollBack();

        throw $e;
    }
}


}