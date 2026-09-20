<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;

/** @var \App\Core\ViewContext $view */
$data = $view->WBData;
// var_dump($data);
?>
<h2>Připraveno ke stornování</h2>
<p>Seznam úkolů a zakázek, které splňují podmínky pro stornování. </p>

<div class="workbench-grid">
<div class="card card-workbench">
    <h3>Úkoly</h3>
    <details>
        <summary>Úkoly, které lze stornovat</summary>
        <ul>
            <li>Nemají žádný report práce.</li>
            <li>Jejich stav musí být: Otevřeno</li>
        </ul>
    </details>
<table>
    <thead>
        <tr>
            <th>Název</th>
            
            <th>Status</th>
            <th>Počet reportů</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['myReadyToCancelTasks'] as $task): ?>
            <tr>
                <td><a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>" 
                                    title="Jít na detail úkolu a zkontrolovat nebo přidat reporty."
                                    >
                                    <?= e($task['title']) ?></a></td>
                
                <td><span class="badge badge-status-<?= e($task['status']) ?>">
                    <?= te($task['status']) ?>
                </span></td>
                <td><?= e($task['assignments_count'] ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="card card-workbench">
    <h3>Zakázky</h3>
    <details>
        <summary>Zakázky, které lze stornovat</summary>
        <ul>
            <li>Nemají žádný úkol.</li>
            <li>Jejich stav musí být: Nový nebo Probíhá.</li>
            <li>Všechny jejich úkoly musí být ve stavu „Stornováno“, případně zakázka nesmí mít žádný úkol.</li>
        </ul>
    </details>
    <table>
        <thead>
            <tr>
                <th>Název</th>
                <th>Popis</th>
                <th>Status</th>
                <th>Počet úkolů u zakázky</th>
                <th>K uzavření</th>
                <th>Ke stornování</th>
            </tr>
        </thead>
    <tbody>
        <?php foreach ($data['myReadyToCancelOrders'] as $orders): ?>
            <tr>
                <td>[<?= e($orders['id']) ?>] <a href="<?= Url::to('/{tenant}/work-orders/' .  $orders['id'] . '/detail/#main') ?>" 
                                    title="Jít na detail zakázky a zkontrolovat její úkoly."
                                    >
                                    <?= e($orders['title']) ?></a></td>
                <td><?= e($orders['description'] ?? '') ?></td>
                <td><span class="badge badge-status-<?= e($orders['status']) ?>">
                    <?= te($orders['status']) ?>
                </span></td>
                <td><?= count($orders['tasks'] ?? []) ?></td>
                <td><?= e($orders['can_be_done'] ? 'Ano' : 'Ne') ?></td>
                <td><?= e($orders['can_be_cancelled'] ? 'Ano' : 'Ne') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

        </div><!-- .workbench-grid -->