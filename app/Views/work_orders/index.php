<?php
declare(strict_types=1); 
$css = '';
//require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$workOrders = $view->orders;
//var_dump($workOrders);
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
                <span class="badge badge-priority-<?= e($wo['priority']) ?>">
                    <?= te($wo['priority']) ?>
                </span>
            </div>

            <!-- TĚLO KARTY -->
            <div class="card-body">
                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-label">Status</span>
                        <span class="badge badge-status-<?= e($wo['status']) ?>"><?= te($wo['status']) ?></span>
                    </div>
                    <!-- Sem můžeš přidat další metadata, až budou -->
                    <div class="meta-item">
                       <span class="meta-label">Celkem úkolů: </span>
                        <span class="meta-value"><?= e($wo['tasks_total']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Otevřených: </span>
                        <span class="meta-value"><?= e($wo['tasks_open']) ?></span>
                     </div>
                    <div class="meta-item">
                       <span class="meta-label">Uzavřených: </span>
                        <span class="meta-value"><?= e($wo['tasks_done']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Zrušených: </span>
                        <span class="meta-value"><?= e($wo['tasks_cancelled']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Napsáno reportů: </span>
                        <span class="meta-value"><?= e($wo['reports_count']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Najeto: </span>
                        <span class="meta-value"><?= e($wo['total_km']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Odpracováno: </span>
                        <span class="meta-value"><?= e($wo['total_hours_formatted']) ?></span>
                    </div>
               </div>
            </div>

            <!-- PATIČKA KARTY -->
            <div class="card-footer">
                <div class="actions">
                    <a href="<?= Url::to('/{tenant}/work-orders/' . (int)$wo['id'] . '/detail/#main') ?>" 
                       class="btn btn-secondary" 
                       title="Detail zakázky, můžete přidat úkol k zakázce">
                        🔍 Detail zakázky
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