<?php
declare(strict_types=1);

// views/teams/create.php – vytvoření týmu

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;


require __DIR__ . '/../../layout/header.php';

$data   = $view->data;
$errors = $view->errors;
?>


<h1>Detail exportu</h1>

<p>
    Období:
    <?= htmlspecialchars($data['period_from']) ?>
    -
    <?= htmlspecialchars($data['period_to']) ?>
</p>

<p>
    Poznámka:
    <?= htmlspecialchars($data['note'] ?? '') ?>
</p>

<hr>

<h2>Položky</h2>

<?php if ($data['items'] === []): ?>
    <p>Žádné položky</p>
<?php else: ?>

<table border="1" cellpadding="5">
    <tr>
        <th>Úkol</th>
        <th>Název</th>
        <th>Uživatel</th>
        <th>Minuty</th>
        <th>Km</th>
    </tr>

<?php foreach ($data['items'] as $item): ?>
    <tr>
        <td><?= $item['task_id'] ?></td>
        <td><?= htmlspecialchars($item['task_title']) ?></td>
        <td><?= htmlspecialchars($item['user_name']) ?></td>
        <td><?= $item['minutes_spent'] ?></td>
        <td><?= $item['kilometers'] ?></td>
    </tr>
<?php endforeach; ?>

</table>

<?php endif; ?>