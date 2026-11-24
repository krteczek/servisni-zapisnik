<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;

$data = $view->WBData;
?>
<h2>Úkoly k fakturaci (mé) [<?= count($data['myInvoiceToReady']) ?>]</h2>
<p>Úkoly, které splňují podmínky pro fakturaci.</p>

<?php if (!$data['isInternalBilling'] && !$data['isExternalAccounting']): ?>
    <p>Pro zobrazení úkolů k fakturaci je potřeba <a href="<?= Url::to('/{tenant}/settings/billing/#main') ?>">nastavit způsob fakturace</a>.</p>
<?php elseif ($data['myInvoiceToReady'] === []): ?>
    <p>Žádné úkoly k fakturaci.</p>
<?php else: ?>
<div class="workbench-grid workbench-grid--full">
    <div class="card card-workbench">
        <table>
            <thead>
                <tr>
                    <th>Zakázka</th>
                    <th>Název úkolu</th>
                    <th>Dokončeno</th>
                    <th>Hodiny</th>
                    <th>km</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['myInvoiceToReady'] as $task): ?>
                    <tr>
                        <td><a href="<?= Url::to('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/') ?>"><?= e($task['work_order_title'] ?? '-') ?></a></td>
                        <td><?= e($task['title']) ?></td>
                        <td><?= formatCzDate($task['done_at']) ?></td>
                        <td><?= formatMinutes((int)$task['total_minutes']) ?></td>
                        <td><?= e($task['total_kilometers']) ?></td>
                        <td>
                            <?php if ($data['isInternalBilling']): ?>
                                <a href="<?= Url::to('/{tenant}/billing/invoice/draft/create/from-task/' . $task['id'] . '/?open=my-tasks-invoice#main') ?>" title="Vytvořit fakturu">Fakturovat</a>
                            <?php elseif ($data['isExternalAccounting']): ?>
                                <a href="<?= Url::to('/{tenant}/billing/export/draft/create/from-task/' . $task['id'] . '/?open=my-tasks-invoice#main') ?>" title="Vytvořit export">Exportovat</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>