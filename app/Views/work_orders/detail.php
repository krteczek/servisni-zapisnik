<?php
declare(strict_types=1);
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
?>
<?= $css ?>

<div class="wo-detail">

    <!-- HEADER -->
    <div class="wo-detail-header">
        <div class="wo-detail-title">
            Zakázka #<?= (int) $order['id'] ?> – <?= htmlspecialchars($order['title'] ?? '') ?>
        </div>

        <div class="wo-detail-meta">
            <span class="wo-status status-open" title="Status zakázky">Otevřená</span>
            <span class="wo-priority priority-medium" title="Priorita zakázky">Střední</span>
        </div>
    </div>

    <!-- BASIC INFO -->
<div class="wo-section">
    <h3>Základní informace</h3>

    <div class="wo-info-main">
        <strong>Popis: </strong><br>
        <?= nl2br(htmlspecialchars($order['description'] ?: 'Nezadán')) ?>
    </div>

    <div class="wo-info-grid">
        <div class="wo-info-box">
            <span class="label">Zdroj: </span>
            <span class="value">
                <?= htmlspecialchars($workOrder['source'] ?: 'Nezadán') ?>
            </span>
        </div>

        <div class="wo-info-box">
            <span class="label">Požadavek vznesl: </span>
            <span class="value">
                <?= htmlspecialchars($workOrder['requested_by'] ?: 'Nezadán') ?>
            </span>
        </div>

        <div class="wo-info-box">
            <span class="label">Kontakt: </span>
            <span class="value">
                <?= htmlspecialchars($workOrder['contact'] ?: 'Nezadán') ?>
            </span>
        </div>
    </div>
</div>




<div class="wo-section">
    <h3>Úkoly k zakázce</h3>
<div class="task-form">

    <div class="task-form-context">
        Zakázka: <strong><?= htmlspecialchars($order['title']) ?></strong>
    </div>

    <h2>Nový úkol</h2>

<?php
$ch = '';
if ($err) {
    foreach ($err as $field => $messages) {
        foreach ($messages as $msg) {
            $ch .= '
<p>' . e($msg) . '</p>
';
        }
    }
}	
?>

    <form method="post" action="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/tasks/create') ?>">
    <?= Csrf::getField() ?>
<?= e($ch) ?>
        <div class="form-group">
            <label>Název úkolu <span class="req">*</span></label>
            <input type="text" name="title" value="<?= e($post['title']) ?>"  required>
        </div>

        <div class="form-group">
            <label>Popis</label>
            <textarea name="description"><?= e($post['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Tým <span class="req">*</span></label>
            <select name="team_id" required>
                <option value="">— vyber tým —</option>
            <?php foreach ($teams as $team): ?>
                <option
                    value="<?= $team['id'] ?>"
                    <?= (($post['team_id'] ?? null) == $team['id']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($team['name']) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>


        <div class="form-actions">
            <button class="btn btn-success">Vytvořit úkol</button>
        </div>

    </form>
</div>


<?php if ($tasks === []) : ?>
    <p class="muted">Zatím nejsou přidány žádné úkoly.</p>
<?php else : ?>

    <div class="task-list">

<?php foreach ($tasks as $task) : ?>
        <div class="task-box status-<?= htmlspecialchars($task['status']) ?>">

            <!-- HLAVIČKA TASKU -->
            <div class="task-header">
                <strong>
                    
                    <?= htmlspecialchars($task['title'] ?? 'Bez názvu') ?>
                </strong>

                <span class="task-status">
                    <?= htmlspecialchars($task['status']) ?>
                </span>
            </div>

            <!-- META -->
            <div class="task-meta">
                Vytvořeno:
                <?= htmlspecialchars(formatCzDate($task['created_at'])) ?>
            </div>

            <!-- STATISTIKY ASSIGNMENTŮ -->
            
<!-- tady potřebuji ty hodiny a kilometry --> 
<div class="task-stats">
    <span>Záznamy: <?= $task['stats']['total'] ?></span>
    <span>Čas: <?= formatMinutes($task['stats']['minutes'] ?? 0) ?></span>
    <span>Km: <?= $task['stats']['kilometers'] ?? 0 ?></span>
</div>
            

            <!-- AKCE NAD TASKEM -->
            <div class="task-actions">

                <a class="btn btn-sm btn-secondary"
							href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/tasks/' . $task['id']) ?>">
							Detail / assignmenty
					</a>


					<a class="btn btn-sm btn-primary"
					   href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/tasks/' . $task['id'] . '/edit') ?>">
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