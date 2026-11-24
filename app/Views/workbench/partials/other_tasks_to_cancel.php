<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;
use App\Core\Csrf;

$data = $view->WBData;
$otherTeamsById = array_column($data['otherTeams'], null, 'id');
?>
<h2>Úkoly připravené ke stornování (ostatní) [<?= count($data['otherReadyToCancelTasks']) ?>]</h2>
<p>Úkoly, které nemají žádný report práce a lze je stornovat.</p>

<?php if ($data['otherReadyToCancelTasks'] === []): ?>
    <p>Žádné úkoly ke stornování.</p>
<?php else: ?>
<div class="workbench-grid workbench-grid--full">
    <div class="card card-workbench">
        <table>
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Tým</th>
                    <th>Status</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['otherReadyToCancelTasks'] as $task): ?>
                    <tr>
                        <td><a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>"><?= e($task['title']) ?></a></td>
                        <td><span class="team-dot" style="background: <?= e($otherTeamsById[$task['team_id']]['color'] ?? '') ?>"></span> <?= e($otherTeamsById[$task['team_id']]['name'] ?? '') ?></td>
                        <td><span class="badge badge-status-<?= e($task['status']) ?>"><?= te($task['status']) ?></span></td>
                        <td>
                            <form action="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/cancel?open=other-tasks-cancel') ?>" method="post" onsubmit="return confirm('Opravdu chcete stornovat úkol <?= e($task['title']) ?>?');">
                                <input type="hidden" name="_method" value="DELETE">
                                <?= Csrf::getField() ?>
                                <button type="submit" class="btn btn-danger">Stornovat</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>