<?php
declare(strict_types=1);

use App\Core\Url;

$css = '';
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

$tasks = $view->tasks ?? [];
?>

<?= $css ?>

<div class="tasks">
    <?php foreach ($tasks as $task): ?>
        <div class="task-card">

            <div class="task-header">
                <span class="task-title">
                    <?= htmlspecialchars($task['title']) ?>
                </span>

                <span class="task-status status-<?= htmlspecialchars($task['status']) ?>">
                    <?= strtoupper($task['status']) ?>
                </span>
            </div>

            <div class="task-meta">
                <?php if (!empty($task['work_order_id'])): ?>
                    <span class="task-order">
                        Zakázka #<?= (int)$task['work_order_id'] ?>: <?= htmlspecialchars($task['work_order_title']) ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($task['team_name'])): ?>
                    <span class="task-team">
                        Tým: <?= htmlspecialchars($task['team_name']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="task-actions">
                <a
                    href="<?= Url::to('/tasks/' . (int)$task['id'] . '/edit') ?>"
                    class="task-action"
                    title="Upravit úkol"
                >✏️</a>

                <a
                    href="<?= Url::to('/tasks/' . (int)$task['id'] . '/report') ?>"
                    class="task-action"
                    title="Přidat report"
                >📝</a>
            </div>

        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
