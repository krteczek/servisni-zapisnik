<?php require __DIR__ . '/../layout/header.php';
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
$workOrders = $view->orders;
?>
<h1>Zakázky</h1>

<table class="table"  border="1" cellpadding="8">
    <thead>
        <tr>
            <th>#</th>
            <th>Externí číslo</th>
            <th>Název</th>
            <th>Priorita</th>
            <th>Stav</th>
            <th>Zdroj</th>
            <th>Vytvořeno</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($workOrders)): ?>
            <tr>
                <td colspan="8" style="text-align:center;">
                    Žádné zakázky
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($workOrders as $wo): ?>
                <tr>
                    <td><?= (int) $wo['id'] ?></td>
                    <td><?= htmlspecialchars($wo['external_number']) ?></td>
                    <td><?= htmlspecialchars($wo['title']) ?></td>
                    <td><?= htmlspecialchars($wo['priority']) ?></td>
                    <td><?= htmlspecialchars($wo['status']) ?></td>
                    <td><?= htmlspecialchars($wo['source']) ?></td>
                    <td><?= htmlspecialchars($wo['created_at']) ?></td>
                    <td>
                        <a href="<?= Url::to('/work-orders/' . (int) $wo['id']) ?>">
                            detail
                        </a>
                        <a href="<?= Url::to('/work-orders/update/' . (int) $wo['id']) ?>">
                            detail
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<p>
    <a href="<?= Url::to('/work-orders') ?>" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>
<?php require __DIR__ . '/../layout/footer.php'; ?>