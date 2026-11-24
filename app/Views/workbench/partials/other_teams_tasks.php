<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;

$data = $view->WBData;
?>
<h2>Otevřené úkoly pro ostatní týmy [<?= count($data['otherTeamTasks']) ?>]</h2>
<p>Úkoly, které jsou přiřazené týmům, kterých nejste členem.</p>

<?php if ($data['otherTeamTasks'] === []): ?>
    <p>Žádné otevřené úkoly.</p>
<?php else: ?>
<div class="workbench-grid workbench-grid--full">
    <div class="card card-workbench">
        <table>
            <thead>
                <tr>
                    <th>Priorita</th>
                    <th>Název úkolu</th>
                    <th>Tým</th>
                    <th>Zakázka</th>
                    <th>Termín</th>
                    <th>Opakující se</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['otherTeamTasks'] as $task): ?>
                    <tr>
                        <td><span class="badge badge-priority-<?= e($task['work_order_priority']) ?>"><?= te($task['work_order_priority']) ?></span></td>
                        <td>
                            <a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>"><?= e($task['title']) ?></a>
                            <a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/report/#main') ?>" target="_blank" rel="noopener noreferrer" class="open-new-tab"> ( ↗ ) </a>
                        </td>
                        <td><span class="team-dot" style="background: <?= e($task['team_color']) ?>"></span> <?= e($task['team_name']) ?></td>
                        <td>
                            <a href="<?= Url::to('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main') ?>"><?= e($task['work_order_title']) ?></a>
                            <a href="<?= Url::to('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main') ?>" target="_blank" rel="noopener noreferrer" class="open-new-tab"> ( ↗ ) </a>
                        </td>
                        <td><?= e($task['due_date'] ?? 'Neuveden') ?></td>
                        <td>
                            <?php if ($task['task_type'] === 'recurring_master'): ?>
                                <a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/recurringEdit/#main') ?>" class="btn btn-secondary" target="_blank">🔁 ↗</a>
                            <?php elseif ($task['task_type'] === 'recurring_instance'): ?>
                                <span title="Vygenerováno automaticky">↻</span>
                            <?php else: ?>
                                ---
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>