<?php 
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
$workOrders = $view->orders;
?>
<div class="work-orders">

    <?php foreach ($workOrders as $wo): ?>
        <div class="work-order-card">

            <div class="wo-header">
                <div class="wo-title">
                    <?= htmlspecialchars($wo['title']) ?>
                </div>

                <div class="wo-priority priority-<?= $wo['priority'] ?>">
                    <?= strtoupper($wo['priority']) ?>
                </div>
            </div>

            <div class="wo-meta">
                <span class="wo-status status-<?= $wo['status'] ?>">
                    <?= strtoupper($wo['status']) ?>
                </span>
            </div>

            <div class="wo-actions">
                <a href="<?= Url::to('/work-orders/' . (int)$wo['id']) ?>" class="btn btn-primary">
                    Detail
                </a>

                <a href="<?= Url::to('/work-orders/update/' . (int)$wo['id']) ?>" class="btn btn-secondary">
                    Upravit
                </a>
            </div>

        </div>
    <?php endforeach; ?>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>