<?php
declare(strict_types=1);

// views/teams/create.php – vytvoření týmu

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;


require __DIR__ . '/../../layout/header.php';

$data   = $view->data ?? [];
$errors = $view->errors ?? [];
?>
<h1>Exporty fakturace</h1>

<a href="<?= Url::to('/{tenant}/exports/billing/create') ?>">+ Nový export</a>

<?php if (empty($data)): ?>
    <p>Žádné exporty</p>
<?php else: ?>

<div class="list">
<?= count($row['items'] ?? []) ?>
<?php foreach ($data as $row): ?>
    <div class="card">

        <div>
            <strong>Období:</strong>
            <?= htmlspecialchars($row['period_from']) ?>
            -
            <?= htmlspecialchars($row['period_to']) ?>
        </div>

        <div>
            <strong>Vytvořeno:</strong>
            <?= htmlspecialchars($row['created_at'] ?? '') ?>
        </div>

        <div>
            <a href="/<?= $tenant ?>/exports/billing/<?= $row['id'] ?>/detail">
                Detail
            </a>

            <a href="/<?= $tenant ?>/exports/billing/<?= $row['id'] ?>/pdf">
                PDF
            </a>
        </div>

    </div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../../layout/footer.php';


