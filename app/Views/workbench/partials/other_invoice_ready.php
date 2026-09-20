<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

$data = $view->WBData;
//var_dump($data);
?>

<h2>Úkoly a zakázky k fakturaci (ostatní) [<?=  count($data['otherInvoiceToReady']) ?>]</h2>

<table>
    <thead>
        <tr>
            <th>Název úkolu</th>
            <th>stav</th>
            <th>Zakázka</th>        
            <th>Akce</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['otherInvoiceToReady'] as $task): ?>
        <tr>
            <td><?= e($task['title']) ?></td>
            <td><?= e($task['status']) ?></td>
            <td></td>
            <td></td>
        </tr>
        <?php endforeach; ?>
     </tbody>
</table>