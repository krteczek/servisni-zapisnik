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
<h2>Zakázky připravené ke stornování</h2>
<p>Seznam zakázek, které splňují podmínky pro stornování. </p>

<div class="workbench-grid">
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
        <?php if ($data['myReadyToCancelOrders'] === []): ?>
            <p>Žádné zakázky ke stornování.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Popis</th>
                        <th>Status</th>
                        <th>Počet úkolů u zakázky</th>
                        <th>Akce</th>
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
                            <td><form action="<?= Url::to('/{tenant}/work-orders/' . $orders['id'] . '/close/canceled?open=my-prepare-to-cancel') ?>" method="post"
                                    onsubmit="return confirm('Opravdu chcete stornovat zakázku <?= e($orders['title']) ?>?');">
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