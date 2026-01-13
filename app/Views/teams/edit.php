<?php
declare(strict_types=1);

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$team        = $view->team;
$rolesInTeam = $view->rolesInTeam;
?>

<form method="post" action="<?= Url::to('/teams/' . $team['id'] . '/edit') ?>">
    <label>Název</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($team['name']) ?>"><br><br>

    <label>Barva</label><br>
    <input type="color" name="color" value="<?= htmlspecialchars($team['color']) ?>"><br><br>

    <button type="submit">Uložit tým</button>
</form>

<hr>

<h2>Členové týmu</h2>
<table border="1" cellpadding="6">
	<thead>
		<tr>
		<th> Jméno </th>
		<th> funkce v týmu </th>
		<th> Akce </th>
		</tr>
	</thead>
<?php foreach ($view->members as $m): ?>	
<tr>
<td>        <?= htmlspecialchars($m['last_name'] . ' ' . $m['first_name']) ?>
        
</td>
<td>(<?= htmlspecialchars($m['role_in_team']) ?>)</td>
<td>
        <form method="post"
              action="<?= Url::to('/teams/' . $team['id'] . '/edit') ?>"
              style="display:inline">
            <input type="hidden" name="remove_membership_id" value="<?= $m['membership_id'] ?>">
            <button type="submit">Odebrat</button>
        </form>

</td>

</tr>

<?php endforeach; ?>
	
</table>
<hr>

<h3>Přidat člena</h3>
<table border="1" cellpadding="6">
	<thead>
		<tr>
		<th> Jméno </th>
		<th> funkce v týmu </th>
		<th> Akce </th>
		</tr>
	</thead>
<?php foreach ($view->availableUsers as $u): ?>	
<tr>
<td>
	<?= htmlspecialchars($u['last_name'] . ' ' . $u['first_name']) ?>
</td>
<td>
<form method="post"
              action="<?= Url::to('/teams/' . $team['id'] . '/edit') ?>"
              style="display:inline">
              <input type="hidden" name="add_user_id" value="<?= $u['id'] ?>">
              <select name="role_in_team">
                <?php foreach ($rolesInTeam as $key => $label): ?>
                    <option value="<?= $key ?>">
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
</td>
<td>
            <input type="hidden" name="remove_membership_id" value="<?= $m['membership_id'] ?>">
            <button type="submit">×</button>
        </form>
<button type="submit">Přidat</button>
</form>
</td>


</tr>

<?php endforeach; ?>
	
</table>

<ul>
<?php foreach ($view->availableUsers as $u): ?>
    <li>
        <?= htmlspecialchars($u['last_name'] . ' ' . $u['first_name']) ?>

        <form method="post"
              action="<?= Url::to('/teams/' . $team['id'] . '/edit') ?>"
              style="display:inline">

            <input type="hidden" name="add_user_id" value="<?= $u['id'] ?>">

            <select name="role_in_team">
                <?php foreach ($rolesInTeam as $key => $label): ?>
                    <option value="<?= $key ?>">
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Přidat</button>
        
    </li>
<?php endforeach; ?>
</ul>
