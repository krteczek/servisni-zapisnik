<?php
declare(strict_types=1);

//views/teams/edit.php – tým + členové (hlavní obrazovka)

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$team = $view->team;
?>
<hr>

<h2>Členové týmu <?= htmlspecialchars($team['name']) ?></h2>

<table border="1" cellpadding="6">
    <tr>
        <th>Jméno</th>
        <th>Role</th>
        <th>Akce</th>
    </tr>

    <?php foreach ($view->members as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></td>
            <td><?= htmlspecialchars($m['role_in_team']) ?></td>
            <td>
                <form method="post" action="<?= Url::to('/teams/' . $team['id'] . '/edit') ?>">
                    <input type="hidden" name="remove_membership_id" value="<?= $m['id'] ?>">
                    <button type="submit">Odebrat</button>
                </form>
            </td>
        </tr>
    <?php endforeach ?>
</table>

<hr>

<h2>Přidat člena</h2>

<form method="post" action="<?= Url::to('/teams/' . $team['id'] . '/edit') ?>">
    <select name="add_user_id">
        <?php foreach ($view->availableUsers as $u): ?>
            <option value="<?= $u['id'] ?>">
                <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
            </option>
        <?php endforeach ?>
    </select>

    <select name="role_in_team">
        <option value="monter">Montér</option>
        <option value="predak">Předák</option>
        <option value="mistr">Mistr</option>
    </select>

    <button type="submit">Přidat</button>
</form>
