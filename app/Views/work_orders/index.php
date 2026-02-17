<?php
declare(strict_types=1); 
$css = '';
//require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$workOrders = $view->orders;
?>
<?= $css ?>

<div class="entity-grid">
    <?php foreach ($workOrders as $wo): ?>
        <div class="card">
            <!-- HLAVIČKA KARTY -->
            <div class="card-header">
                <span class="card-title">  <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] . '/detail/#main') ?>" 
                   class="card-title"
                   title="Otevřít detail zakázky">
                    <?= e($wo['title']) ?>
                </a></span>
                <span class="badge badge-priority-<?= htmlspecialchars($wo['priority']) ?>">
                    <?= te($wo['priority']) ?>
                </span>
            </div>

            <!-- TĚLO KARTY -->
            <div class="card-body">
                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-label">Status</span>
                        <span class="badge badge-status-<?= htmlspecialchars($wo['status']) ?>">
                            <?= te($wo['status']) ?>
                        </span>
                    </div>
                    <!-- Sem můžeš přidat další metadata, až budou -->
                </div>
            </div>

            <!-- PATIČKA KARTY -->
            <div class="card-footer">
                <div class="actions">
                    <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] . '/detail/#main') ?>" 
                       class="btn btn-secondary" 
                       title="Detail zakázky">
                        🔍 Detail
                    </a>
                    <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] . '/edit/#main') ?>" 
                       class="btn btn-secondary" 
                       title="Upravit zakázku">
                        ✏️ Upravit
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>