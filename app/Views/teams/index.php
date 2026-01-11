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
        <th>Počet členů</th>
        <th>Členové</th>
        <th>Akce</th>
    </tr>

    <?php foreach ($view->teams as $team): ?>
        <tr>
            <td><?= htmlspecialchars($team['name']) ?></td>
            <td>
                <span style="display:inline-block;width:20px;height:20px;background:<?= $team['color'] ?>"></span>
            </td>
            <td><?= $team['members_count'] ?></td>
            <td>
                <?php foreach ($team['members'] as $m): ?>
                    <?= htmlspecialchars($m['last_name'] . ' ' . $m['first_name']) ?><br>
                <?php endforeach; ?>
            </td>
            <td>
                <a href="<?= Url::to('/teams/' . (int)$team['id']  . '/edit') ?>">Upravit</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
