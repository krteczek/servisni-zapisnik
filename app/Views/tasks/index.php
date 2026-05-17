<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$tasks = $view->data;
//var_dump($tasks);
?>

<?php if (empty($tasks)): ?>
    <div class="ui-alert ui-alert-warning">
        <strong>Nemáte žádné úkoly</strong>
        <p>Zatím zde není žádný úkol. Nové úkoly můžete vytvořit vždy jen u <a href="<?= Url::to('/{tenant}/work-orders/#main') ?>">zakázek</a>.</p>
    </div>
<?php else: ?>

<div class="entity-grid">
    <?php foreach ($tasks as $task): ?>
        <div class="card task-card" style="--task-color: <?= e($task['team_color'] ?? '#ccc') ?>;">
            <!-- HLAVIČKA KARTY -->
            <div class="card-header">
                <span class="card-title">
                    <a href="<?= Url::to('/{tenant}/tasks/' . (int)$task['id'] . '/report/#main') ?>"
                       title="Přidat report k tomuto úkolu">
                    <?= e($task['title']) ?>
                </a></span>
                <span class="badge badge-status-<?= e($task['status']) ?>">
                    <?= te($task['status']) ?>
                </span>
<?php if ($task['is_recurring_master']): ?>
<span class="badge badge-recurring">🔁</span>
<?php elseif ($task['is_generated_task']): ?>
<span class="badge badge-recurring">↻</span>
<?php endif; ?>
            </div>

            <!-- TĚLO KARTY -->
            <div class="card-body">
                <div class="meta-list">
                    <?php if (!empty($task['work_order_id'])): ?>
                        <div class="meta-item">
                            <span class="meta-label">Zakázka</span>
                            <span class="meta-value">
                                <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$task['work_order_id'] . '/detail/#main') ?>">
                                    #<?= (int)$task['work_order_id'] ?>: <?= e($task['work_order_title']) ?>
                                </a>
                            </span>
            
                        </div>
                    <?php endif; ?>
							<div class="meta-item">
								<span class="meta-label">Počer reportů: </span>
								<span class="meta-value"><?= (int)$task['reports_count'] ?></span>
							</div>
							<div class="meta-item">
								<span class="meta-label">Počet hodin: </span>
								<span class="meta-value"><?= e($task['total_hours_formatted']) ?></span>
							</div>
							<div class="meta-item">
								<span class="meta-label">Počet kilometrů: </span>
								<span class="meta-value"><?= (int)$task['total_km'] ?></span>
							</div>
						</div>
					</div>

            <!-- PATIČKA KARTY -->
            <div class="card-footer">
                <div class="actions">

						  <a href="<?= Url::to('/{tenant}/tasks/' . (int)$task['id'] . '/edit/#main') ?>" 
                       class="btn btn-secondary" 
                       title="Upravit úkol">
                        ✏️ Upravit úkol
                    </a>
                    <?php if($task['can_add_report'] === true): ?>
                    <a href="<?= Url::to('/{tenant}/tasks/' . (int)$task['id'] . '/report/#main') ?>" 
                       class="btn btn-secondary" 
                       title="Přidat report k tomuto úkolu">
                        📝 Napsat Report
                    </a>
                    <?php endif;?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>