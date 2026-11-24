<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;

$data = $view->WBData;
$myTeamsById = array_column($data['myTeams'], null, 'id');
?>
<h2>Úkoly připravené k dokončení [<?= count($data['myReadyToDoneTasks']) ?>]</h2>
<p>Úkoly, které mají alespoň jeden report práce a lze je uzavřít.</p>

<?php if ($data['myReadyToDoneTasks'] === []): ?>
    <p>Žádné úkoly k dokončení.</p>
<?php else: ?>
<div class="workbench-grid workbench-grid--full">
    <div class="card card-workbench">
        <table>
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Tým</th>
                    <th>Počet reportů</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['myReadyToDoneTasks'] as $task): ?>
                    <tr>
                        <td><a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>"><?= e($task['title']) ?></a></td>
                        <td><span class="team-dot" style="background: <?= e($myTeamsById[$task['team_id']]['color'] ?? '') ?>"></span> <?= e($myTeamsById[$task['team_id']]['name'] ?? '') ?></td>
                        <td><?= e($task['assignments_count'] ?? 0) ?></td>
                        <td><a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>" class="btn btn-primary">Dokončit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>