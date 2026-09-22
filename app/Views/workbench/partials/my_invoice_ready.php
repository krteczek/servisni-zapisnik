<?php
declare(strict_types=1);

use App\Core\Url;


/** @var \App\Core\ViewContext $view */
$data = $view->WBData;
//var_dump($data);
?>

<h2>Úkoly a zakázky k fakturaci (mé) [<?=  count($data['myInvoiceToReady']) ?>]</h2>
<p>Seznam úkolů a zakázek, které splnují podmínky pro fakturaci</p>

<div class="">


        <details>
            <summary>Úkoly a zakázky, které lze fakturova/exportovat</summary>
            <ul>
                <li>Musíte mít nastavenou metodu akturace (interní faktury/exporty pro externí účetnictví)</li>
                <li>Úkol musí být dokončený</li>
                <li>Úkol nesmí být již fakturován/exportován</li>
                <li>Dokončený úkol již nelze upravovat</li>
            </ul>
        </details>
<?php 
/** Ochrana před nenastavením způsobu fakturace */
if (!$data['isInternalBilling'] && !$data['isExternalAccounting']): ?>
    <p>
        Pro zobrazení úkolů a zakázek k fakturaci je potřeba
        <a href="<?= Url::to('/{tenant}/settings/billing/#main') ?>">
        nastavit způsob fakturace
        </a>.
    </p>
<?php else: ?>
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
                    <td><a href="<?=  Url::to('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main') ?>"><?= e($task['work_order_title'] ?? '-') ?></a></td>
                    <td><?= e($task['title']) ?></td>
                    <td><?= formatCzDate($task['done_at']) ?></td>
                    <td><?= formatMinutes((int)$task['total_minutes']) ?></td>
                    <td><?= e($task['total_kilometers']) ?></td>
                    <td>
                        <?php if ($data['isInternalBilling']): ?>
                            <a href="<?= Url::to('/{tenant}/billing/invoice/create/from-task/' . $task['id'] . '/#main') ?>"
                                title="vytvořit fakturu z tohoto úkolu">Fakturovat</a>
                        <?php elseif ($data['isExternalAccounting']): ?>
                            <a href="<?= Url::to('/{tenant}/billing/export/create/from-task/' . $task['id'] . '/#main') ?>"
                                title="vytvořit export z tohoto úkolu">Exportovat</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<?php endif; ?>


</div><!-- .workbench-grid -->