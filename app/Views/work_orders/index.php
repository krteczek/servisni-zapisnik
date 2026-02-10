<?php
declare(strict_types=1); 
$css = '';
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$workOrders = $view->orders;
?>
<?= $css ?>
    <div class="work-orders">
        <?php foreach ($workOrders as $wo): ?>
            <div class="work-order-card">

                <div class="wo-header">
                    <a
                        href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] .'/detail') ?>"
                        class="wo-title"
                        title="Otevřít detail zakázky"
                    >
                        <?= htmlspecialchars($wo['title']) ?>
                    </a>

                    <div class="wo-priority priority-<?= htmlspecialchars($wo['priority']) ?>">
                        <?= strtoupper($wo['priority']) ?>
                    </div>
                </div>

                <div class="wo-meta">
                    <span class="wo-status status-<?= htmlspecialchars($wo['status']) ?>">
                        <?= strtoupper($wo['status']) ?>
                    </span>
                </div>
<div class="wo-kontext">

</div>
                <div class="wo-actions">
                    <a
                        href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] . '/detail/#main') ?>"
                        class="wo-action"
                        title="Detail zakázky"
                    >
                        🔍
                    </a>

                    <a
                        href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id']  . '/edit/#main')?>"
                        class="wo-action"
                        title="Upravit zakázku"
                    >
                        ✏️
                    </a>
                </div>

            </div>
        <?php endforeach; ?>
    </div>


<?php require __DIR__ . '/../layout/footer.php'; ?>
