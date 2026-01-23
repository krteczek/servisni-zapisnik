<?php
declare(strict_types=1);

// views/teams/index.php – přehled týmů

use App\Core\Url;
use App\Core\Access;

require __DIR__ . '/../layout/header.php';
?>

<table border="1" cellpadding="6">
    <thead>
    <tr>
        <th>Název</th>
        <th>Barva</th>
        <th>Počet členů</th>
        <th>Členové</th>
        <th>Akce</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($view->teams as $team): ?>
        <tr>
            <td><?= e($team['name']) ?></td>

            <td>
                <span
                    style="display:inline-block;
                           width:20px;
                           height:20px;
                           background:<?= e($team['color']) ?>">
                </span>
            </td>

            <td><?= (int) $team['members_count'] ?></td>

            <td>
                <?php foreach ($team['members'] as $m): ?>
                    <?= e($m['last_name'] . ' ' . $m['first_name']) ?><br>
                <?php endforeach ?>
            </td>

            <td>
                <?php if (Access::can('teams.edit')): ?>
                    <a href="<?= Url::to('/teams/' . (int) $team['id'] . '/edit') ?>">
                        Upravit
                    </a><br>
                    <a href="<?= Url::to('/teams/toggle/' . $team['id']) ?>"
   onclick="return confirm('Opravdu chcete změnit stav týmu?')">
   <?= $team['active'] ? 'Deaktivovat' : 'Aktivovat' ?>
</a>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>