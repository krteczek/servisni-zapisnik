<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

$data = $view->WBData;
//var_dump($data);
?>

<h2>Úkoly k fakturaci (ostatní) [<?=  count($data['otherInvoiceToReady']) ?>]</h2>
<div class="workbench-grid">
    <div class="card card-workbench">
        <table>
            <thead>
                <tr>
                    <th>Název úkolu</th>
                    <th>Zakázka</th>        
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['otherInvoiceToReady'] as $task): ?>
                <tr>
                    <td><?= e($task['title']) ?></td>            
                    <td></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div><!-- .workbench-grid -->