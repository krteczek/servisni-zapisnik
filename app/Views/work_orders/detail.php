<?php
declare(strict_types=1);

//view/work_orders/detail.php

/** @var \App\Core\ViewContext $view */
require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
//use App\Core\Access;
//use App\Models\Team;
use App\Helpers\RecurringHelper;
use App\Helpers\DateHelper;

use App\Services\Tasks\TaskType;
use App\Services\Tasks\TaskStatus;
use App\Services\WorkOrders\WOStatus;

$order = $view->orderDetail;
$tasks = $view->orderTasks;
$errors = $view->errors;

$err = $view->errors;


/*
echo "Tasks:
<pre>" . var_export($view->tasks, true) . "</pre>";

echo "Order:
<pre>" . var_export($view->order, true) . "</pre>";
echo "Errors: 
<pre>" . var_export($view->errors, true) . "</pre>";
*/
?>

<!--
/**************************************************************
    Základní informace o zakázce
***************************************************************/
-->
<div class="create-container"><!-- HLAVNÍ SLOUPEC – DETAIL ZAKÁZKY -->
    <div class="card"><!-- karta zakázky -->
        <!-- Zobrazení chyb (stejné jako v report šabloně) -->
        <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

        <div class="card-body">
            <!-- HEADER -->
            <div class="card-header">
                #<?= $order['id'] ?>: <?= e($order['title']) ?>
            </div>

            <!-- BASIC INFO -->
            <div class="wo-section">
                <h3>Základní informace</h3>

                <div class="wo-info-box">
                    <span class="label">Zákazník: </span>
                    <span title="Jméno zákazníka, název firmy">
                        <?= e($order['company_name']) ?>
                    </span>
                </div>

                <div class="wo-info-box">
                    <span class="label">Status zakázky: </span>
                    <span
                        class="badge badge-status-<?= e($order['status']) ?>"
                        title="Stav zakázky"
                    >
                        <?= te($order['status']) ?>
                    </span>
                </div>

                <div class="wo-info-box">
                    <span class="label">Priorita zakázky: </span>
                    <span
                        class="badge badge-priority-<?= e($order['priority']) ?>"
                        title="Priorita zakázky"
                    >
                        <?= te($order['priority']) ?>
                    </span>
                </div>

                <div class="wo-info-main">
                    <strong>Popis: </strong><br>
                    <?= tx($order['description'] !== '' ? $order['description'] : 'Nezadán') ?>
                </div>

                <div class="wo-info-grid">
                    <div class="wo-info-box">
                        <span class="label">Zdroj: </span>
                        <span class="value">
                            <?= te($order['source'] !== '' ? $order['source'] : 'Nezadán') ?>
                        </span>
                    </div>

                    <div class="wo-info-box">
                        <span class="label">Požadavek vznesl: </span>
                        <span class="value">
                            <?= e($order['requested_by'] !== '' ? $order['requested_by'] : 'Nezadán') ?>
                        </span>
                    </div>

                    <div class="wo-info-box">
                        <span class="label">Kontakt: </span>
                        <span class="value">
                            <?= e($order['contact_person'] !== '' ? $order['contact_person'] : 'Nezadán') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="meta-item">
            <span class="meta-label">Termín dokončení:</span>
            <span class="meta-value">
                <?= e(formatCzDate($order['wo_due_date'] ?? '')) ?>
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Úkoly (všechny):</span>
            <span class="meta-value">
                <?= $order['total_tasks_count'] ?>
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Úkoly (otevřené):</span>
            <span class="meta-value">
                <?= $order['open_tasks_count'] ?>
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Úkoly (hotové):</span>
            <span class="meta-value">
                <?= $order['done_tasks_count'] ?>
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Úkoly (zrušené):</span>
            <span class="meta-value">
                <?= $order['cancelled_tasks_count'] ?>
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Reporty:</span>
            <span class="meta-value">
                <?= $order['report_count'] ?>
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Čas k dokončení: </span>
            <span class="meta-value">
                <?= ($order['estimated_hours'] !== null ? $order['estimated_hours'] : 0) ?> hodin
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Z toho použité:</span>
            <span class="meta-value">
                <?= e($order['total_time']) ?> hodin
            </span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Km:</span>
            <span class="meta-value">
                <?= $order['total_km'] ?>
            </span>
        </div>

        <div class="card-footer">
            <?php if (WOStatus::isOpen($order['status'])): ?>
                <a
                    href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/edit/#main') ?>"
                    class="btn btn-primary"
                    title="Upravit zakázku"
                >
                    ✏️ Upravit zakázku
                </a>
            <?php endif; ?>

            <?php if (WOStatus::isOpen($order['status'])): ?>
                <a
                    class="btn btn-secondary"
                    href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/tasks/create/#main') ?>"
                >
                    Přidat nový úkol k této zakázce
                </a>
            <?php endif; ?>

            <?php if ($order['ready_for_done']): ?>
                <form
                    method="post"
                    action="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/close/done') ?>"
                    data-confirm="Opravdu chcete zakázku uzavřít jako hotovou?"
                    style="display:inline-block;"
                >
                    <?= Csrf::getField() ?>
                    <button class="btn btn-danger">Uzavřít zakázku</button>
                </form>
            <?php endif; ?>

            <?php if ($order['ready_for_cancel'] && WOStatus::isOpen($order['status'])): ?>
                <form
                    method="post"
                    action="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/close/canceled') ?>"
                    data-confirm="Opravdu chcete zakázku stornovat?"
                    style="display:inline-block;"
                >
                    <?= Csrf::getField() ?>
                    <button
                        class="btn btn-danger"
                        title="Stornovat zakázku"
                    >
                        Stornovat zakázku
                    </button>
                </form>
            <?php endif; ?>

            <a
                href="<?= Url::to('/{tenant}/work-orders') ?>/#main"
                class="btn btn-secondary"
                title="Zpět na přehled zakázek"
            >
                ← Zpět na přehled
            </a>
        </div><!-- /.card-footer -->
    </div><!-- /.card -->

    <!-- PRAVÝ SLOUPEC – NÁPOVĚDA / INFO -->
    <div class="card card-help" id="helpCard">
        <div class="card-body">
            <details>
                <summary>Nápověda ke stavům a typům úkolů</summary>

                <p>Každá zakázka může mít některý z těchto stavů:</p>

                <ul style="padding-left:1.2rem;">
                    <li>
                        <span class="badge badge-status-<?= e('new') ?>">
                            <?= te('new') ?>
                        </span>
                        – Nová zakázka, ještě bez úkolů.
                    </li>
                    <li>
                        <span class="badge badge-status-<?= e('in_progress') ?>">
                            <?= te('in_progress') ?>
                        </span>
                        – Zákázka obsahuje alespoň jeden úkol.
                    </li>
                    <li>
                        <span class="badge badge-status-<?= e('done') ?>">
                            <?= te('done') ?>
                        </span>
                        – všechny úkoly byly dokončeny nebo zrušeny.
                    </li>
                    <li>
                        <span class="badge badge-status-<?= e('exported') ?>">
                            <?= te('exported') ?>
                        </span>
                        – po dokončení byly provedeny exporty dat.
                    </li>
                    <li>
                        <span class="badge badge-status-<?= e('cancelled') ?>">
                            <?= te('cancelled') ?>
                        </span>
                        – Zakázka byla stornována.
                    </li>
                </ul>

                <h4>Typy úkolů</h4>

                <p>Úkoly mohou být tří typů:</p>

                <ul style="padding-left:1.2rem;">
                    <li>běžný úkol</li>
                    <li>
                        <span
                            class="badge badge-recurring"
                            title="opakující se, master"
                        >
                            🔁
                        </span>
                        – Opakující se úkol (master)
                    </li>
                    <li>
                        <span
                            class="badge badge-recurring"
                            title="generovaný úkol"
                        >
                            ↻
                        </span>
                        – Generovaný úkol
                    </li>
                </ul>

                <h5>🔁 Opakující se úkol (master)</h5>

                <p>
                    Slouží jako šablona pro automatické vytváření dalších úkolů.
                </p>

                <ul>
                    <li>lze jej upravovat</li>
                    <li>nelze k němu přidávat reporty</li>
                    <li>neslouží k evidenci práce</li>
                    <li>určuje pravidla opakování</li>
                </ul>

                <h5>↻ Generovaný úkol</h5>

                <p>
                    Úkol automaticky vytvořený z opakujícího se master úkolu.
                </p>

                <ul>
                    <li>nelze jej upravovat</li>
                    <li>lze k němu přidávat reporty</li>
                    <li>slouží k evidenci skutečně provedené práce</li>
                </ul>

                <h5>Běžný úkol</h5>

                <p>Standardní ručně vytvořený úkol.</p>

                <ul>
                    <li>lze jej upravovat</li>
                    <li>lze k němu přidávat reporty</li>
                    <li>slouží k evidenci skutečně provedené práce</li>
                </ul>

                <h5>Přístup k detailu a reportům</h5>

                <p>Detail a reporty jsou dostupné pouze pro:</p>

                <ul>
                    <li>běžné úkoly</li>
                    <li>generované úkoly</li>
                </ul>

                <p>
                    🔁 Master úkoly pro opakování neslouží k vykazování práce,
                    a proto k nim reporty nejsou dostupné.
                </p>
            </details>
        </div><!-- /.card-body -->
    </div><!-- /.card-help -->
</div><!-- /.create-container -->


<!-- **************************************************************
    Seznam úkolů pro zakázku
*************************************************************** -->

<?php if ($tasks === []) : ?>

    <p class="muted">Zatím nejsou přidány žádné úkoly.</p>

<?php else : ?>

<div class="task-grid" id="task-list"><!-- GRID S ÚKOLY -->

    <?php foreach ($tasks as $task) : ?>

        <?php
        $canCloseDone = $task['can_close'];
        $canCloseCanceled = $task['can_cancel'];

        $canEdit = !TaskStatus::isClosed($task['status']);
        ?>

        <div
            class="task-card card status-<?= e($task['status']) ?>"
            id="taskId_<?= $task['id'] ?>"
            style="--task-color: <?= e($task['team_color']) ?>"
        >

            <div class="card-header">

                <?php if (TaskType::isMaster($task['task_type'])): ?>

                    <span
                        class="badge badge-recurring"
                        title="opakující se, master"
                    >
                        🔁
                    </span>

                <?php elseif (TaskType::isInstance($task['task_type'])): ?>

                    <span
                        class="badge badge-recurring"
                        title="generovaný úkol"
                    >
                        ↻
                    </span>

                <?php endif; ?>

                <strong>
                    <?= e($task['title']) ?>
                </strong>

                <span class="badge badge-status-<?= e($task['status']) ?>">
                    <?= te($task['status']) ?>
                </span>
            </div>

            <div class="card-body">

                <?php
                $deadlineClass = 'deadline-none';
                $deadlineText = 'Neuvedeno';

                if ($task['due_date'] !== null && $task['due_date'] !== '') {
                    $today = date('Y-m-d');
                    $due = date('Y-m-d', strtotime($task['due_date']));

                    $deadlineText = date(
                        'd.m.Y',
                        strtotime($task['due_date'])
                    );

                    if ($due < $today) {
                        $deadlineClass = 'deadline-late';
                    } elseif ($due === $today) {
                        $deadlineClass = 'deadline-today';
                    } else {
                        $deadlineClass = 'deadline-future';
                    }
                }
                ?>

                <div class="meta-item">
                    <span class="meta-label">Termín:</span>

                    <span class="meta-value <?= $deadlineClass ?>">
                        <?= e($deadlineText) ?>
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">Úkol řeší tým:</span>

                    <span class="meta-value">
                        <?= te($task['team_name']) ?>
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">Popis:</span>

                    <span class="meta-value">
                     <?= tx($task['description'] !== null && $task['description'] !== '' ? $task['description'] : 'Bez popisu') ?>                   </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">Vytvořeno:</span>

                    <span class="meta-value">
                        <?= e(formatCzDate($task['created_at'])) ?>
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">Reporty:</span>

                    <span class="meta-value">
                        <?= ($task['stats']['assignments_count']) ?>
                    </span>
                </div>

                <?php if (TaskType::isMaster($task['task_type'])): ?>

                    <div class="card-section">
                        <span class="section-label">
                            Nastavení generování opakování
                        </span>

                        <p>
                            Generuje se
                            <?= RecurringHelper::describe(
                                $task['recurring_frequency_type'],
                                $task['recurring_frequency_value']
                            ) ?>
                            <br>

                            Další vytvoření:
                            <?= RecurringHelper::nextDueDate(
                                $task['recurring_next_due_date']
                            ) ?>
                            <br>

                            vytvoří se:
                            <?= RecurringHelper::warningText(
                                $task['recurring_warning_days_before']
                            ) ?>

                            Stav:
                            <?= $task['recurring_active'] === 1
                                ? 'Aktivní'
                                : 'Neaktivní' ?>
                        </p>
                    </div>

                <?php else: ?>

                    <div class="meta-item">
                        <span class="meta-label">Čas:</span>

                        <span class="meta-value">
                            <?= formatMinutes($task['stats']['total_minutes']) ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">Km:</span>

                        <span class="meta-value">
                            <?= $task['stats']['total_km'] ?>
                        </span>
                    </div>

                <?php endif; ?>

            </div>

            <div class="card-footer">

                <!--
                    Detail úkolu a reporty:
                    - normal: ano
                    - recurring_master: ne
                    - recurring_instance: ano
                -->
                <?php if (!TaskType::isMaster($task['task_type'])): ?>

                    <?php
                    $url = '';
                    $text1 = '';
                    $text2 = '';

                    if (TaskStatus::isDone($task['status'])) {
                        $url = Url::to(
                            '/{tenant}/archive/task/' .
                            $task['id'] .
                            '/reports/#main'
                        );

                        $text1 = 'do archivu';
                        $text2 = 'Archiv úkolu s reporty';

                    } elseif (TaskStatus::isCancelled($task['status'])) {
                        $url = Url::to(
                            '/{tenant}/archive/task/' .
                            $task['id'] .
                            '/reports/#main'
                        );

                        $text1 = 'do archivu';
                        $text2 = 'Archiv zrušeného úkolu bez reportů';

                    } else {
                        $url = Url::to(
                            '/{tenant}/tasks/' .
                            $task['id'] .
                            '/add-report/#main'
                        );

                        $text1 = 'na detail';
                        $text2 = 'Detail + reporty';
                    }
                    ?>

                    <a
                        class="btn btn-sm btn-secondary"
                        href="<?= $url ?>"
                        title="Jít <?= $text1 ?> tohoto úkolu a reporty"
                    >
                        <?= $text2 ?>
                    </a>

                <?php endif; ?>


                <!--
                    Úprava úkolu:
                    - normal: ano
                    - recurring_master: ano
                    - recurring_instance: ne

                    Navíc pouze pokud není úkol uzavřený.
                -->
                <?php if ($canEdit && TaskType::isEditable($task['task_type'])): ?>

                    <a
                        href="<?= Url::to(
                            '/{tenant}/tasks/' .
                            $task['id'] .
                            '/edit/#main'
                        ) ?>"
                        class="btn btn-secondary"
                        title="Upravit úkol"
                    >
                        ✏️ Upravit úkol
                    </a>

                <?php endif; ?>


                <!--
                    Nastavení opakování je dostupné pouze pro master úkol.
                -->
                <?php if ($canEdit && TaskType::isMaster($task['task_type'])): ?>

                    <a
                        href="<?= Url::to(
                            '/{tenant}/tasks/' .
                            $task['id'] .
                            '/recurring/#main'
                        ) ?>"
                        class="btn btn-secondary"
                        title="Nastavení opakování pro šablonu opakovaného úkolu"
                    >
                        Nastavení opakování
                    </a>

                <?php endif; ?>


                <?php if (
                    $task['can_cancel']
                    && !TaskStatus::isCancelled($task['status'])
                    && !TaskStatus::isDone($task['status'])
                ): ?>

                    <?php if ($task['is_generated_task'] === 0): ?>

                        <form
                            method="post"
                            action="<?= Url::to(
                                '/{tenant}/tasks/' .
                                $task['id'] .
                                '/cancel/#main'
                            ) ?>"
                            data-confirm="Opravdu chcete úkol stornovat?"
                        >
                            <?= Csrf::getField() ?>

                            <button
                                class="btn btn-sm btn-danger"
                                title="Stornovat úkol"
                            >
                                Stornovat
                            </button>
                        </form>

                    <?php endif; ?>

                <?php endif; ?>


                <?php if (
                    $task['can_close']
                    && !TaskStatus::isDone($task['status'])
                ): ?>

                    <?php
                    if (TaskType::isMaster($task['task_type'])) {
                        $url = '/{tenant}/tasks/' .
                            $task['id'] .
                            '/recurring-done/#main';
                    } else {
                        $url = '/{tenant}/tasks/' .
                            $task['id'] .
                            '/done/#main';
                    }
                    ?>

                    <form
                        method="post"
                        action="<?= Url::to($url) ?>"
                        data-confirm="Opravdu chcete úkol uzavřít?"
                    >
                        <?= Csrf::getField() ?>

                        <button
                            class="btn btn-sm btn-success"
                            title="Uzavřít úkol"
                        >
                            Uzavřít
                        </button>
                    </form>

                <?php endif; ?>


                <?php if (
                    TaskType::isMaster($task['task_type'])
                    && !$canEdit
                    && in_array(
                        $task['status'],
                        [
                            TaskStatus::DONE,
                            TaskStatus::CANCELLED,
                        ],
                        true
                    )
                ): ?>

                    <p>
                        Tato šablona pro generování opakovaných úkolů již
                        nemůže být upravována, protože byla uzavřena.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    <?php endforeach; ?>

</div><!-- /.task-grid -->

<?php endif; ?>

<p>
    <a
        href="<?= Url::to('/{tenant}/work-orders/#main') ?>"
        class="btn btn-secondary"
    >
        ← Zpět na přehled
    </a>
</p>

<?php require __DIR__ . '/../layout/footer.php';