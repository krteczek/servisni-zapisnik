<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

$data = $view->data;
//var_dump($data);
?>

<h2>Úkoly a zakázky k fakturaci (mé) [<?=  count($data['myInvoiceToReady']) ?>]</h2>
<p>Seznam úkolů a zakázek, které splnují podmínky pro fakturaci</p>

<div class="">


        <details>
            <summary>Úkoly a zakázky, které lze fakturovat</summary>
            <ul>
                <li>Úkol musí být dokončený</li>
                <li>Úkol nesmí být již fakturován/exportován</li>
                <li>Dokončený úkol již nelze upravovat</li>
            </ul>
        </details>

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
                    <td><?= formatMinutes(((int) $task['total_minutes']) ?? 0) ?></td>
                    <td><?= e($task['total_kilometers']) ?></td>
                    <td><a href="<?= Url::to('/{tenant}/billing/invoice/create/task/' . $task['id']) ?>"
                            title="vytvořit fakturu z tohoto úkolu">Fakturovat</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>



</div><!-- .workbench-grid