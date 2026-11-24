<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;

$data = $view->WBData;
?>
<h2>Zakázky připravené k dokončení [<?= count($data['ordersToDone']) ?>]</h2>
<p>Zakázky, které mají všechny úkoly dokončené nebo stornované a alespoň jeden dokončený.</p>

<?php if ($data['ordersToDone'] === []): ?>
    <p>Žádné zakázky k dokončení.</p>
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
                <?php foreach ($data['ordersToDone'] as $order): ?>
                    <tr>
                        <td>[<?= e($order['id']) ?>] <a href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/detail/#main') ?>"><?= e($order['title']) ?></a></td>
                        <td><?= e($order['description'] ?? '') ?></td>
                        <td><span class="badge badge-status-<?= e($order['status']) ?>"><?= te($order['status']) ?></span></td>
                        <td><?= count($order['tasks'] ?? []) ?></td>
                        <td><a href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/detail/#main') ?>" class="btn btn-primary">Dokončit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>