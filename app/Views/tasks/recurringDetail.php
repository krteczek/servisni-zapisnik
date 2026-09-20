<?php
declare(strict_types=1);
// app/views/tasks/report.php

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;
use App\Helpers\RecurringHelper;

require __DIR__ . '/../layout/header.php';

$task = $view->task;
$data = $view->data;
$team = $view->team;
$order = $view->order;
$errors = $view->errors; // chyby validace
/**
 * RecurringDetail doplnit o další informace:
  - historie generování
  - počet vygenerovaných úkolů
  - datum posledního vytvoření
  - datum posledního dokončení
  - souhrn reportů generovaných úkolů
  - audit změn nastavení
  - datum deaktivace / ukončení
  * to platí i pro šablonu
 */
//dd($task, $data);
?>
<div class="create-container">
<!-- DETAIL ŠABLONY -->
<div class="card task-card"  style="--task-color: <?= e($team['color'] ?? '#ccc') ?>;">

    <div class="card-header">
        <h2>Detail nastavení šablony</h2>
    </div>

    <div class="card-body">
        <div class="card-section">
            <span class="section-label">Stav šablony: </span>
            <?php if ($data['active'] === 1): ?>
                <span class="badge badge-status-open">Aktivní</span>
            <?php else: ?>
                <span class="badge badge-status-cancelled">Neaktivní</span>
            <?php endif; ?>
            <br>
            <span class="section-label">Text názvu generovaných úkolů: </span>
            <p><?= e($task['title']) ?></p>
        </div>

        <div class="card-section">
            <span class="section-label">Text popisu generovaných úkolů: </span>
            <?= tx($task['description'] ?? 'Neuvedeno') ?>
        </div>

        <div class="card-section">
            <span class="section-label">Tým, který má tyto úkoly na starosti: </span>
            <?= tx($team['name'] ?? 'Neuvedeno') ?>
        </div>

        <div class="card-section">
            <span class="section-label">Jak často se úkoly vytvářejí: </span>
            <p><?= RecurringHelper::describe($data['frequency_type'] ?? '', $data['frequency_value'] ?? 1) ?></p>
            
           
        </div>

        <div class="card-section">
            <span class="section-label">Další vytvoření</span>
            <p><?= RecurringHelper::nextDueDate($data['next_due_date'] ?? '') ?></p>
        </div>

        <div class="card-section">
            <span class="section-label">Vytvořit předem</span>
            <p>
                <?= RecurringHelper::warningText($data['warning_days_before'] ?? 0) ?>
                
            </p>
        </div>
    </div>

<!-- ZDROJOVÁ ZAKÁZKA -->
    <div class="card-header">
        <h3>Zdrojová zakázka</h3>
    </div>

    <div class="card-body">

        <p>
            #<?= $task['work_order_id'] ?>
            : <?= e($order['title'] ?? '') ?>
            <span class="badge badge-status-<?= e($order['status']) ?>">
                <?= te($order['status']) ?>
            </span>
        </p>
        

        <a
            href="<?= Url::to('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#taskId_' . $task['id']) ?>"
            class="btn btn-secondary"
        >
            Otevřít zakázku
        </a>

    </div>



<!-- AKCE -->


    <div class="card-header">
        <h3>Akce</h3>
    </div>

    <div class="card-body">

        <div class="form-actions">

            <a
                href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/recurring/#main') ?>"
                class="btn btn-primary"
            >
                Upravit generování šablony
            </a>

            <a
                href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>"
                class="btn btn-secondary"
            >
                Upravit podrobnosti úkolu
            </a>

            <?php if ($data['active'] === 1): ?>

                <a
                    href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/recurring/#main') ?>"
                    class="btn btn-warning"
                >
                    Deaktivovat
                </a>

            <?php else: ?>

                <a
                    href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/recurring/#main') ?>"
                    class="btn btn-success"
                >
                    Aktivovat
                </a>

            <?php endif; ?>

            <?php if ($data['active'] === 0): ?>

                <form
                    method="post"
                    action="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/recurring-done/#main') ?>"
                    data-confirm="Opravdu chcete ukončit tuto šablonu?"
                >
                    <?= \App\Core\Csrf::getField() ?>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Ukončit šablonu
                    </button>
                </form>

            <?php endif; ?>

        </div>

    </div>

</div>


</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>