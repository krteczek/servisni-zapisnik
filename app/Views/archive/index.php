<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$data = $view->data;
$type = $view->type;
//var_dump($tasks);
require __DIR__ . '/_filters.php';
?>
<?php if ($type === 'tasks'): ?>
    <div class="entity-grid">
        <?php foreach ($data as $task): ?>
            <div class="card task-card">

                <!-- HEADER -->
                <div class="card-header">
                    <span class="card-title">
                        <a href="<?= Url::to('/{tenant}/tasks/' . (int)$task['id'] . '/detail/#main') ?>">
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
                                <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$task['work_order_id'] . '/detail/#main') ?>">
                                    #<?= (int)$task['work_order_id'] ?>:
                                    <?= e($task['work_order_title'] ?? 'Neznámá') ?>
                                </a>
                            </span>
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="card-footer">
                    <div class="actions">

                        <a href="<?= Url::to('/{tenant}/tasks/' . (int)$task['id'] . '/detail/#main') ?>"
                        class="btn btn-secondary">
                            🔍 Detail
                        </a>
<?php if ($order['status'] === 'new' || $order['status'] === 'in_progress') : ?>
                        <a href="<?= Url::to('/{tenant}/tasks/' . (int)$task['id'] . '/clone/#main') ?>"
                        class="btn btn-secondary">
                            ♻️ Klonovat
                        </a>
                        <?php else : ?>
                            <p>Zakázka je již uzavřenaa nelze ji klonovat.</p>
                            
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>

    <div class="entity-grid">
        <?php foreach ($data as $wo): ?>
            <div class="card">

                <div class="card-header">
                    <span class="card-title">
                        <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] . '/detail/#main') ?>">
                            <?= e($wo['title']) ?>
                        </a>
                    </span>

                    <span class="badge badge-status-<?= e($wo['status']) ?>">
                        <?= te($wo['status']) ?>
                    </span>
                </div>

                <div class="card-footer">
                    <div class="actions">
                        <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] . '/detail/#main') ?>"
                        class="btn btn-secondary">
                            🔍 Detail
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>