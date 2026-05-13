<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$data = $view->data;
//var_dump($data);
?>
<style>
    table, th, td {
        border: 1px solid #ccc;
        border-collapse: collapse;

    }
    tbody tr:nth-child(even) {
    background: #fafafa;
}
</style>
<div class="workbench-container">
    <h1>Pracovní plocha</h1>

    <h2>Moje týmy</h2>
    <ul>
        <?php foreach ($data['myTeams'] as $team): ?>
            <li>
                <span class="team-dot" style="background: <?= e($team['color']) ?>"></span>
                <?= e($team['name']) ?> (Role: <?= e($team['role_in_team']) ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Otevřené úkoly pro mé týmy</h2>
 
<table>
<thead>
    <tr>
        <th>Priorita</th>
        <th>Název úkolu</th>
        <th>Tým</th>
        <th>Zakázka</th>
        
        <th>Termín</th>
        <th>Akce</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($data['myTeamTasks'] as $task): ?>
        <tr>
            <td><span class="badge badge-priority-<?= e($task['work_order_priority']) ?>">
                    <?= te($task['work_order_priority']) ?>
                </span></td>
            <td><?= e($task['title']) ?></td>
            <td><span class="team-dot" style="background: <?= e($task['team_color']) ?>"></span> <?= e($task['team_name']) ?></td>
            <td><?= e($task['work_order_title']) ?></td>
            
            <td><?= e($task['due_date'] ?? 'Bez termínu') ?></td>
            <td>
                <a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>">Detail</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
</table>
    <!-- Další sekce pro jiné úkoly a opakující se úkoly by šly sem -->

<?php require __DIR__ . '/../layout/footer.php'; ?>