<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;
use App\Core\Csrf;

$data = $view->WBData;
?>
<h2>Zakázky připravené ke stornování [<?= count($data['ordersToCancel']) ?>]</h2>
<p>Zakázky, které nemají žádný dokončený úkol a všechny jejich úkoly jsou stornované (nebo žádné nemají).</p>

<?php if ($data['ordersToCancel'] === []): ?>
    <p>Žádné zakázky ke stornování.</p>
<?php else: ?>
<div class="workbench-grid workbench-grid--full">
    <div class="card card-workbench">
        <table>
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Popis</th>
                    <th>Stav</th>
                    <th>Počet úkolů</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['ordersToCancel'] as $order): ?>
                    <tr>
                        <td>[<?= e($order['id']) ?>] <a href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/detail/#main') ?>"><?= e($order['title']) ?></a></td>
                        <td><?= e($order['description'] ?? '') ?></td>
                        <td><span class="badge badge-status-<?= e($order['status']) ?>"><?= te($order['status']) ?></span></td>
                        <td><?= count($order['tasks'] ?? []) ?></td>
                        <td>
                            <form action="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/close/canceled?open=orders-to-cancel') ?>" method="post" onsubmit="return confirm('Opravdu chcete stornovat zakázku <?= e($order['title']) ?>?');">
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