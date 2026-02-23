<?php
declare(strict_types=1);

//view/work_orders/detail.php
$css = '';
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
use App\Models\Team;


/** @var array $order */
$workOrder = $this->view->order;

/** @var array[] $tasks */
$tasks = $this->view->tasks ?? [];
$order = $view->order;
$teams = $view->teams;
$post = $view->post;
$canCloseDone = $workOrder['canCloseDone'];
$canCloseCanceled = $workOrder['canCloseCanceled'];

$err = $view->errors; 
var_dump($order);
?>
<?= $css ?>
<!--
/**************************************************************	
	Základní informace o zakázce
***************************************************************/
-->
<div class="wo-detail">

    <!-- HEADER -->
    <div class="wo-detail-header">
        <div class="wo-detail-title">
            Zakázka #<?= (int) $order['id'] ?> – <?= e($order['title'] ?? '') ?>
        </div>

        <div class="wo-detail-meta">
					<span class="badge badge-status-<?= e($order['status']) ?>"><?= te($order['status']) ?></span>
					<span class="badge badge-priority-<?= e($order['priority']) ?>"><?= te($order['priority']) ?></span>

        </div>
    </div>

    <!-- BASIC INFO -->
<div class="wo-section">
    <h3>Základní informace</h3>

    <div class="wo-info-main">
        <strong>Popis: </strong><br>
        <?= nl2br(e($order['description'] ?: 'Nezadán')) ?>
    </div>

    <div class="wo-info-grid">
        <div class="wo-info-box">
            <span class="label">Zdroj: </span>
            <span class="value">
                <?= te($workOrder['source'] ?: 'Nezadán') ?>
            </span>
        </div>

        <div class="wo-info-box">
            <span class="label">Požadavek vznesl: </span>
            <span class="value">
                <?= e($workOrder['requested_by'] ?: 'Nezadán') ?>
            </span>
        </div>

        <div class="wo-info-box">
            <span class="label">Kontakt: </span>
            <span class="value">
                <?= e($workOrder['contact'] ?: 'Nezadán') ?>
            </span>
        </div>
    </div>
</div>

<!-- 
	**************************************************************	
		Formulář pro přidání nebo úpravu tesku (úkolu) k zakázce
	***************************************************************
-->



<div class="wo-section">
    <h3>Úkoly k zakázce</h3>
<div class="task-form">
<style>
.task-add-button {
padding: 14px;
}
</style>
<div class="task-box">
                <a class="btn btn-primary"
							href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/tasks/create/#main') ?>">
							Přidat nový úkol k této zakázce
					</a>


    <a href="<?= Url::to('/{tenant}/work-orders') ?>/#main" class="btn btn-secondary">
        ← Zpět na přehled
    </a>

</div>
<?php if ($tasks === []) : ?>
    <p class="muted">Zatím nejsou přidány žádné úkoly.</p>
<?php else : ?>


    <div class="task-list">

<?php foreach ($tasks as $task) : ?>
        <div class="task-box status-<?= e($task['status']) ?>" id="taskId_<?= (int) $task['id'] ?>">

            <!-- HLAVIČKA TASKU -->
            <div class="task-header">
                <strong>                   
                    <?= htmlspecialchars($task['title'] ?? 'Bez názvu') ?>
                </strong>
					<span class="badge badge-priority-<?= e($task['status']) ?>">
                    <?= te($task['status']) ?>
                </span>
           </div>
				<div class="task-meta">
				<strong class="task-description-header">Popis úkolu:</strong>
				<div class="task-description"><?= e($task['description']) ?></div>
				</div>
            <!-- META -->
            <div class="task-meta">
                Vytvořeno:
                <?= e(formatCzDate($task['created_at'])) ?>
            </div>

            <!-- STATISTIKY ASSIGNMENTŮ -->
            
<!-- tady potřebuji ty hodiny a kilometry --> 
<div class="task-stats"><?php print_r($task); ?>
    <span>Záznamy: <?= $task['stats']['total_assignments'] ?></span>
    <span>Čas: <?= formatMinutes($task['stats']['total_minutes'] ?? 0) ?></span>
    <span>Km: <?= $task['stats']['total_kilometers'] ?? 0 ?></span>
</div>
            

            <!-- AKCE NAD TASKEM -->
            <div class="task-actions">

                <a class="btn btn-sm btn-secondary"
							href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>">
							Detail / assignmenty
					</a>


					<a class="btn btn-sm btn-primary"
					   href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/edit/#main') ?>">
					    Upravit
					</a>
<?php if ($task['can_cancel']) : ?>
                <form method="post"
								action="<?= Url::to(
								'/{tenant}/work-orders/' . $order['id'] . '/tasks/' . $task['id'] . '/cancel'
								) ?>"
								onsubmit="return confirm('Opravdu chcete úkol stornovat?');"
								style="display:inline">

                    <?= Csrf::getField() ?>
                    <button class="btn btn-sm btn-danger">
                        Stornovat
                    </button>
                </form>
<?php endif; ?>

<?php if ($task['can_close']) : ?>
                <form method="post"
								action="<?= Url::to(
								'/{tenant}/work-orders/' . $order['id'] . '/tasks/' . $task['id'] . '/done'
								) ?>"
								onsubmit="return confirm('Opravdu chcete úkol uzavřít jako hotový?');"
								style="display:inline">

                    <?= Csrf::getField() ?>
                    <button class="btn btn-sm btn-success">
                        Uzavřít
                    </button>
                </form>
<?php endif; ?>

            </div>

        </div>
<?php endforeach; ?>

    </div>

<?php endif; ?>
</div>

<p>
    <a href="<?= Url::to('/{tenant}/work-orders') ?>/#main" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>