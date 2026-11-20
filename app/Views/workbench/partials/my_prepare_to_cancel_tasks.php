<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;
use App\Core\Csrf;

/** @var \App\Core\ViewContext $view */
$data = $view->WBData;
$myTeamsById    = array_column($data['myTeams'], null, 'id');
$otherTeamsById = array_column($data['otherTeams'], null, 'id');
$allTeamsById   = $myTeamsById + $otherTeamsById;

//var_dump($data);
?>
<h2>Úkoly připravené ke stornování</h2>
<p>Seznam úkolů, které splňují podmínky pro stornování. </p>

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
        <?php if ($data['myReadyToCancelTasks'] === []): ?>
            <p>Žádné úkoly ke stornování.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Tým</th>
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
                        <td><span class="team-dot" style="background: <?= e($myTeamsById[$task['team_id']]['color']) ?>"></span></td>

                        <td><span class="badge badge-status-<?= e($task['status']) ?>">
                            <?= te($task['status']) ?>
                        </span></td>
                        <td><form action="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/cancel?open=my-prepare-to-cancel') ?>" method="post"
                                onsubmit="return confirm('Opravdu chcete stornovat úkol <?= e($task['title']) ?>?');">
                                <input type="hidden" name="_method" value="DELETE">
                                <?= Csrf::getField() ?>
                                <button type="submit" class="btn btn-danger">Stornovat</button>
                            </form></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div><!-- .workbench-grid -->