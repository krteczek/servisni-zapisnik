<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$data = $view->WBData;

$myTeamsById    = array_column($data['myTeams'], null, 'id');
$otherTeamsById = array_column($data['otherTeams'], null, 'id');
$allTeamsById   = $myTeamsById + $otherTeamsById;
// var_dump($data);
?>

<div class="workbench-container">
    <div class="workbench-teams-grid">
        <div class="card card-wb-teams">
            <details>
                <summary>Moje týmy</summary>
                <?php if ($data['myTeams'] === []): ?>
                    <p>Nejste členem žádného týmu.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($data['myTeams'] as $team): ?>
                            <li><span class="team-dot" style="background: <?= e($team['color']) ?>"></span><?= e($team['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </details>
        </div>
        <div class="card card-wb-teams">
            <details>
                <summary>Ostatní týmy</summary>
                <?php if ($data['otherTeams'] === []): ?>
                    <p>Neexistují žádné další týmy.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($data['otherTeams'] as $team): ?>
                            <li><span class="team-dot" style="background: <?= e($team['color']) ?>"></span><?= e($team['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </details>
        </div>
    </div>

    <h2>Důležité akce</h2>
    <div class="card card-workbench-container">
        <!-- ============ ÚROVEŇ 1: Moje / Ostatní ============ -->
        <div class="tab-level" data-level="1">
            <button class="tab-button" data-target="my" title="Akce pro týmy, kterých jsem účastníkem">Moje týmy</button>
            <button class="tab-button" data-target="other" title="Akce pro ostatní týmy">Ostatní týmy</button>
            <button class="tab-button" data-target="orders">Zakázky [<?= count($data['ordersInProgress']) . ' + ' . count($data['ordersToDone']) . ' + ' . count($data['ordersToCancel']) . ' + ' . count($data['ordersToInvoice']) ?>]</button>
            <!-- ============ MOJE ============ -->

            <!-- ---- MOJE → ÚKOLY ---- -->
            <div id="my" class="tab-content">
                <div class="tab-level" data-level="2">
                    <button class="tab-button" data-target="my-tasks-open">Otevřené [<?= count($data['myTeamTasks']) ?>]</button>
                    <button class="tab-button" data-target="my-tasks-done">K dokončení [<?= count($data['myReadyToDoneTasks']) ?>]</button>
                    <button class="tab-button" data-target="my-tasks-cancel">Ke stornování [<?= count($data['myReadyToCancelTasks']) ?>]</button>
                    <button class="tab-button" data-target="my-tasks-invoice">K fakturaci [<?= count($data['myInvoiceToReady']) ?>]</button>

                    <div id="my-tasks-open" class="tab-content">
                        <?php require __DIR__ . '/partials/my_teams_tasks.php'; ?>
                    </div>
                    <div id="my-tasks-done" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/my_tasks_to_done.php'; ?>
                    </div>
                    <div id="my-tasks-cancel" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/my_tasks_to_cancel.php'; ?>
                    </div>
                    <div id="my-tasks-invoice" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/my_invoice_ready.php'; ?>
                    </div>
                </div>
            </div>
            <!-- ============ OSTATNÍ ============ -->
            <!-- ---- OSTATNÍ → ÚKOLY ---- -->
            <div id="other" class="tab-content" hidden>
                <div class="tab-level" data-level="2">
                    <button class="tab-button" data-target="other-tasks-open">Otevřené [<?= count($data['otherTeamTasks']) ?>]</button>
                    <button class="tab-button" data-target="other-tasks-done">K dokončení [<?= count($data['otherReadyToDoneTasks']) ?>]</button>
                    <button class="tab-button" data-target="other-tasks-cancel">Ke stornování [<?= count($data['otherReadyToCancelTasks']) ?>]</button>
                    <button class="tab-button" data-target="other-tasks-invoice">K fakturaci [<?= count($data['otherInvoiceToReady']) ?>]</button>

                    <div id="other-tasks-open" class="tab-content">
                        <?php require __DIR__ . '/partials/other_teams_tasks.php'; ?>
                    </div>
                    <div id="other-tasks-done" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/other_tasks_to_done.php'; ?>
                    </div>
                    <div id="other-tasks-cancel" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/other_tasks_to_cancel.php'; ?>
                    </div>
                    <div id="other-tasks-invoice" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/other_invoice_ready.php'; ?>
                    </div>
                </div>
            </div>

            <!-- ---- ZAKÁZKY ---- -->
            <div id="orders" class="tab-content" hidden>
                <div class="tab-level" data-level="2">
                    <button class="tab-button" data-target="orders-in-progress">Otevřené [<?= count($data['ordersInProgress']) ?>]</button>
                    <button class="tab-button" data-target="orders-to-done">K dokončení [<?= count($data['ordersToDone']) ?>]</button>
                    <button class="tab-button" data-target="orders-to-cancel">Ke stornování [<?= count($data['ordersToCancel']) ?>]</button>
                    <button class="tab-button" data-target="orders-to-invoice">K fakturaci [<?= count($data['ordersToInvoice']) ?>]</button>

                    <div id="orders-in-progress" class="tab-content">
                        <?php require __DIR__ . '/partials/orders_in_progress.php'; ?>
                    </div>
                    <div id="orders-to-done" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/orders_to_done.php'; ?>
                    </div>
                    <div id="orders-to-cancel" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/orders_to_cancel.php'; ?>
                    </div>
                    <div id="orders-to-invoice" class="tab-content" hidden>
                        <?php require __DIR__ . '/partials/orders_to_invoice.php'; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php';