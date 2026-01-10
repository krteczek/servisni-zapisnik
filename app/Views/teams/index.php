<?php
declare(strict_types=1);

//views/teams/index.php – přehled týmů

use App\Core\Url;

require __DIR__ . '/../layout/header.php';
?>

<table border="1" cellpadding="6">
    <tr>
        <th>Název</th>
        <th>Barva</th>
        <th>Členové</th>
        <th>Akce</th>
    </tr>

    <?php foreach ($view->teams as $team): ?>
        <tr>
            <td><?= htmlspecialchars($team['name']) ?></td>
            <td>
                <span style="background:<?= htmlspecialchars($team['color']) ?>; padding:4px 10px;">
                    <?= htmlspecialchars($team['color']) ?>
                </span>
            </td>
            <td><?= (int) $team['members'] ?></td>
            <td>
                <a href="<?= Url::to('/teams/' . $team['id'] . '/edit') ?>">Upravit</a>
            </td>
        </tr>
    <?php endforeach ?>
</table>
