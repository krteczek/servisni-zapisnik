<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
use App\Core\Url;

$data = $view->WBData;
?>
<h2>Zakázky připravené k fakturaci</h2>

<?php if ($data['ordersToInvoice'] === []): ?>
    <p>Žádné zakázky k fakturaci.</p>
<?php else: ?>
<div class="workbench-grid">
    <div class="card card-workbench">    
        <table>
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Popis</th>
                    <th>Stav</th>
                    <th>Počet úkolů</th>
                    <th>K uzavření</th>
                    <th>Ke stornování</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['myReadyToDoneOrders'] as $order): ?>
                    <tr>
                        <td>[<?= e($order['id']) ?>] <a href="<?= Url::to('/{tenant}/work-orders/' . $order['id'] . '/detail/#main') ?>"><?= e($order['title']) ?></a></td>
                        <td><?= e($order['description'] ?? '') ?></td>
                        <td><span class="badge badge-status-<?= e($order['status']) ?>"><?= te($order['status']) ?></span></td>
                        <td><?= count($order['tasks'] ?? []) ?></td>
                        <td><?= $order['can_be_done'] ? 'Ano' : 'Ne' ?></td>
                        <td><?= $order['can_be_cancelled'] ? 'Ano' : 'Ne' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div><!-- .workbench-grid -->
<?php endif; ?>