<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;
use App\Services\Tasks\TaskType;

require __DIR__ . '/../layout/header.php';


// TODO: udělat ikonku pro normální task
$tasks = $view->archiveTasks;
//dc($tasks);
require __DIR__ . '/_filters.php';
?>
<div class="entity-grid">
    <?php foreach ($tasks as $task): ?>

        <?php
            $recurringBadge = '';
            if(TaskType::isInstance($task['task_type'])) {
                $recurringBadge = '<span 
                                        class="badge badge-recurring" 
                                        title="Vygenerovaný opakující se úkol"
                                        >↻</span>';
            } elseif (TaskType::isMaster($task['task_type'])) {
                $recurringBadge = '<span class="badge badge-recurring" 
                                        title="Generátor opakujícího se úkolu"
                                        >🔁</span>';
                //continue;
            }
        ?>
        <div class="card task-card" style="--task-color: <?= e($task['team_color']) ?>;">

            <!-- HEADER -->
            <div class="card-header">
                <span class="card-title">
                    <?= $recurringBadge ?>
                    <a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>"
                        title="zobrazit detail úkolu">
                        <?= e($task['title']) ?>
                    </a>
                </span>

                <span class="badge badge-status-<?= e($task['status']) ?>">
                    <?= te($task['status']) ?>
                </span>
            </div>

            <!-- BODY -->
            <div class="card-body">
                <div class="meta-list">

                    <div class="meta-item">
                        <span class="meta-label">Zakázka</span>
                        <span class="meta-value">
                            <a href="<?= Url::to('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main') ?>"
                                title="zobrazit detail zakázky">
                                #<?= $task['work_order_id'] ?>:
                                <?= e($task['work_order_title']) ?>
                            </a>
                        </span>
                    </div>

                </div>
            </div>

            <!-- FOOTER -->
            <div class="card-footer">
                <div class="actions">

                    <a href="<?= Url::to('/{tenant}/archive/task/' . $task['id'] . '/reports/#main') ?>"
                    class="btn btn-secondary">
                        🔍 Archiv (Detail + reporty)
                    </a>
                    <?php if ($task['work_order_status'] === 'new' || $task['work_order_status'] === 'in_progress') : ?>
                        <a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/clone/#main') ?>"
                            class="btn btn-secondary">
                        ♻️ Klonovat
                        </a>
                    <?php else : ?>
                        <button class="btn btn-secondary disabled"
                            title="Zakázka je uzavřená, nelze klonovat"
                            tabindex="-1"
                            aria-disabled="true"
                            disabled>
                            ♻️ <s>Klonovat</s>
                        </button>
                        
                    <?php endif; ?>

                </div>
            </div>

        </div>
    <?php endforeach; ?>
</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>