<?php
declare(strict_types=1);

//views/teams/edit.php – tým + členové (hlavní obrazovka)

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$team = $view->team;
$rolesInTeam = $view->rolesInTeam;
?>

<form method="post" action="<?= Url::to('/teams/' . $team['id']  . '/edit') ?>">
    <label>Název</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($team['name']) ?>"><br><br>

    <label>Barva</label><br>
    <input type="color" name="color" value="<?= $team['color'] ?>"><br><br>

    <button type="submit">Uložit tým</button>
</form>

<hr>

<h2>Členové týmu</h2>

<ul>
<?php foreach ($view->members as $m): ?>
    <li>
        <?= htmlspecialchars($m['last_name'] . ' ' . $m['first_name']) ?>
        <form method="post" action="<?= Url::to('/teams/' . $team['id']  . '/edit')  ?>" style="display:inline">
            <input type="hidden" name="remove_membership_id" value="<?= $m['membership_id'] ?>">
            <button type="submit">×</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>

<h3>Přidat člena</h3>

<form method="post" action="<?= Url::to('/teams/' . $team['id']  . '/edit')  ?>">
<?php foreach ($view->availableUsers as $u): ?>
	<input type="hidden" name="add_user_id" value="<?= $m['membership_id'] ?>">
	<?= htmlspecialchars($u['last_name'] . ' ' . $u['first_name']) ?>
	<select name="role_in_team">
    <?php foreach ($rolesInTeam as $key => $label): ?>
        <option value="<?= $key ?>">
            <?= htmlspecialchars($label) ?>
        </option>
    <?php endforeach; ?>
</select><br>
<?php endforeach; ?>

	<?php
	/**
    <select name="add_user_id">
        <?php foreach ($view->availableUsers as $u): ?>
            <option value="<?= $u['id'] ?>">
                <?= htmlspecialchars($u['last_name'] . ' ' . $u['first_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
**/
?>
<select name="role_in_team">
    <?php foreach ($rolesInTeam as $key => $label): ?>
        <option value="<?= $key ?>">
            <?= htmlspecialchars($label) ?>
        </option>
    <?php endforeach; ?>
</select>

    <button type="submit">Přidat</button>
</form>
