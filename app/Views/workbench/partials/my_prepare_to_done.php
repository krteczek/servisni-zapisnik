<?php
declare(strict_types=1); 

use App\Core\Url;

/** @var \App\Core\ViewContext $view */
$data = $view->WBData;
//var_dump($data['myReadyToDoneOrders']);

$myTeams = [];
foreach ($data['myTeams'] as $team) {
$myTeams[$team['id']]['name'] = $team['name'];
$myTeams[$team['id']]['color'] = $team['color'];
}
?>

<h2>Připraveno k uzavření</h2>
<p>Seznam úkolů a zakázek, které splňují podmínky pro uzavření. Zkontrolujte jejich reporty a 
    pokud jsou všechny splněné, můžete je uzavřít.
</p>
<div class="workbench-grid">
<div class="card card-workbench">
    <h3>Úkoly</h3>
    <details>
        <summary>Úkoly, které lze uzavřít</summary>
        <ul>
            <li>Mají alespoň jeden report práce.</li>
            <li>Jejich stav musí být: Otevřeno.</li>
            <li>Opakující se úkoly, které slouží jako šablona, lze uzavírat v nastavení opakujícího se úkolu.</li>
        </ul>
    </details>
<table>
    <thead>
        <tr>
            <th>Název</th>
            <th>Tým</th>
            <th>Počet reportů</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['myReadyToDoneTasks'] as $task): ?>
            <tr>
                <td><a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>" title="Jít na detail úkolu a zkontrolovat nebo přidat reporty."><?= e($task['title']) ?></a></td>
                <td><span class="team-dot" style="background: <?= e($myTeams[$task['team_id']]['color']) ?>"></span> <?= te($myTeams[$task['team_id']]['name']) ?></td>
                <td><?= e($task['assignments_count'] ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="card card-workbench">
    <h3>Zakázky</h3>
    <table>
        <thead>
            <tr>
                <th>Název</th>                
                <th>Popis</th>
                <th>Stav</th>
                <th>K uzavření</th>
                <th>Ke stornování</th>
            </tr>
        </thead>
    <tbody>
        <?php foreach ($data['myReadyToDoneOrders'] as $orders): ?>
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
