<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;

$data = $view->WBData;
$myTeamsById = array_column($data['myTeams'], null, 'id');
?>
<h2>Úkoly připravené k dokončení</h2>
<p>Úkoly, které mají alespoň jeden report práce a lze je uzavřít.</p>

<?php if ($data['myReadyToDoneTasks'] === []): ?>
    <p>Žádné úkoly k dokončení.</p>
<?php else: ?>
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
                <td><a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>"><?= e($task['title']) ?></a></td>
                <td><span class="team-dot" style="background: <?= e($myTeamsById[$task['team_id']]['color'] ?? '') ?>"></span> <?= e($myTeamsById[$task['team_id']]['name'] ?? '') ?></td>
                <td><?= e($task['assignments_count'] ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>