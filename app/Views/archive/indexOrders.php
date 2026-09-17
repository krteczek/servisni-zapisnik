<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;
use App\Services\Tasks\TaskType;

require __DIR__ . '/../layout/header.php';

$orders = $view->archiveOrders;
//var_dump($orders);
require __DIR__ . '/_filters.php';
?>


    <div class="entity-grid">
        <?php foreach ($orders as $wo): ?>
            <div class="card">

                <div class="card-header">
                    <span class="card-title">
                        <a href="<?= Url::to('/{tenant}/work-orders/' . $wo['id'] . '/detail/#main') ?>">
                            <?= e($wo['title']) ?>
                        </a>
                    </span>

                    <span class="badge badge-status-<?= e($wo['status']) ?>">
                        <?= te($wo['status']) ?>
                    </span>
                </div>

                <div class="card-footer">
                    <div class="actions">
                        <a href="<?= Url::to('/{tenant}/work-orders/' . $wo['id'] . '/detail/#main') ?>"
                        class="btn btn-secondary">
                            🔍 Detail
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

