<?php
declare(strict_types=1);

//view/work_orders/detail.php

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
use App\Models\Team;



/** @var array[] $tasks */
$order = $view->order;
$tasks = $view->tasks ?? [];
$teams = $view->teams;


$err = $view->errors; 
//var_dump($order, $tasks,$teams);
?>


<!--
/**************************************************************	
	Základní informace o zakázce
***************************************************************/
-->
<div class="create-container">
	<div class="card">
		<div class="card-body">
			<!-- HEADER -->
			<div class="card-header">
				#<?= (int) $order['id'] ?>: <?= e($order['title'] ?? '') ?>
			</div>

			<!-- BASIC INFO -->
			<div class="wo-section">
				<h3>Základní informace</h3>
				<div class="wo-info-box">
					<span class="label">Status zakázky: </span>
					<span class="badge badge-status-<?= e($order['status']) ?>" title="Stav zakázky"><?= te($order['status']) ?></span>
				</div>

				<div class="wo-info-box">
					<span class="label">Priorita zakázky: </span>
					<span class="badge badge-priority-<?= e($order['priority']) ?>" title="Priorita zakázky"><?= te($order['priority']) ?></span>
				</div>
				<div class="wo-info-main">
						<strong>Popis: </strong><br>
						<?= tx($order['description'] ?? '') ?: 'Nezadán' ?>
				</div>

				<div class="wo-info-grid">
					<div class="wo-info-box">
						<span class="label">Zdroj: </span>
						<span class="value"><?= te($order['source'] ?: 'Nezadán') ?></span>
					</div>

					<div class="wo-info-box">
						<span class="label">Požadavek vznesl: </span>
						<span class="value"><?= e($order['requested_by'] ?: 'Nezadán') ?></span>
					</div>

					<div class="wo-info-box">
						<span class="label">Kontakt: </span>
						<span class="value"><?= e($order['contact'] ?: 'Nezadán') ?></span>
					</div>
				</div>
			</div>
		</div>
<div class="card-footer">
                <a class="btn btn-primary"
							href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/tasks/create/#main') ?>">
							Přidat nový úkol k této zakázce
					</a>


    <a href="<?= Url::to('/{tenant}/work-orders') ?>/#main" class="btn btn-secondary">
        ← Zpět na přehled
    </a>

</div>
	</div>
    <!-- PRAVÝ SLOUPEC – NÁPOVĚDA / INFO (volitelně) -->
    <div class="card card-help" id="helpCard">
        <div class="card-body">
            <h3>Nápověda</h3>
				<p>Každá zakázka může mít některý z těchto stavů:</p>
            <ul style="padding-left:1.2rem;">
                <li><span class="badge badge-status-<?= e('new') ?>"><?= te('new') ?></span> – Nová zakázka, bez úkolů.</li>
                <li><span class="badge badge-status-<?= e('in_progress') ?>"><?= te('in_progress') ?></span> – Zákázka má úkoly.</li>
                <li><span class="badge badge-status-<?= e('done') ?>"><?= te('done') ?></span> – Zakázka je dokončena.</li>
                <li><span class="badge badge-status-<?= e('exported') ?>"><?= te('exported') ?></span> – Byly provedeny exporty z aplikace po dokončení zakázky.</li>
                <li><span class="badge badge-status-<?= e('cancelled') ?>"><?= te('cancelled') ?></span> – Zakázka byla zrušena</li>
            </ul>
        </div>
    </div>

<?php if ($tasks === []) : ?>
    <p class="muted">Zatím nejsou přidány žádné úkoly.</p>
<?php else : ?>


    <div class="task-list">

<?php foreach ($tasks as $task) : ?>
<?php
$canCloseDone = $task['can_close'];
$canCloseCanceled = $task['can_cancel'];
?>
       <div class="task-box status-<?= e($task['status']) ?>" id="taskId_<?= (int) $task['id'] ?>">

            <!-- HLAVIČKA TASKU -->
            <div class="task-header">
                <strong>                   
                    <?= e($task['title'] ?? 'Bez názvu') ?>
                </strong>
					<span class="badge badge-status-<?= e($task['status']) ?>">
                    <?= te($task['status']) ?>
                </span>
           </div>
				<div class="task-meta">
				<strong class="task-description-header">Popis úkolu:</strong>
				<div class="task-description"><?= tx($task['description']) ?></div>
				</div>
            <!-- META -->
            <div class="task-meta">
                Vytvořeno:
                <?= e(formatCzDate($task['created_at'])) ?>
            </div>

            <!-- STATISTIKY ASSIGNMENTŮ -->
            
<!-- tady potřebuji ty hodiny a kilometry --> 
<div class="task-stats">
    <span>Záznamy: <?= $task['stats']['assignments_count'] ?></span>
    <span>Čas: <?= formatMinutes($task['stats']['total_minutes'] ?? 0) ?></span>
    <span>Km: <?= $task['stats']['total_kilometers'] ?? 0 ?></span>
</div>
            

            <!-- AKCE NAD TASKEM -->
            <div class="task-actions">

                <a class="btn btn-sm btn-secondary"
							href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>">
							Detail + reporty
					</a>


					<a class="btn btn-sm btn-primary"
					   href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/edit/#main') ?>">
					    Upravit
					</a>
<?php if ($task['can_cancel']) : ?>
                <form method="post"
								action="<?= Url::to(
								'/{tenant}/tasks/' . $task['id'] . '/cancel'
								) ?>"
								onsubmit="return confirm('Opravdu chcete úkol stornovat?');"
								style="display:inline">

                    <?= Csrf::getField() ?>
                    <button class="btn btn-sm btn-danger">
                        Stornovat
                    </button>
                </form>
<?php endif; ?>

<?php if ($task['can_close'] && $task['status'] !== 'done') : ?>
                <form method="post"
								action="<?= Url::to(
								'/{tenant}/tasks/' . $task['id'] . '/done'
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