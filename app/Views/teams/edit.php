<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$team        = $view->team;
$rolesInTeam = $view->rolesInTeam;
?>

<!-- ===================== -->
<!-- ÚPRAVA TÝMU -->
<!-- ===================== -->
<form method="post" action="<?= Url::current() ?>">
    <?= Csrf::getField(); ?>

    <label>Název</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($team['name']) ?>"><br><br>

    <label>Barva</label><br>
    <input type="color" name="color" value="<?= htmlspecialchars($team['color']) ?>"><br><br>

    <button type="submit">Uložit tým</button>
</form>

<hr>

<!-- ===================== -->
<!-- ČLENOVÉ TÝMU -->
<!-- ===================== -->
<h2>Členové týmu</h2>

<table border="1" cellpadding="6">
<thead>
<tr>
    <th>Jméno</th>
    <th>Členem týmů</th>
    <th>Role v týmu</th>
	<th>Změna role</th>
    <th>Akce</th>
</tr>
</thead>

<?php foreach ($view->members as $m): ?>
<tr>
    <td><?= htmlspecialchars($m['last_name'] . ' ' . $m['first_name']) ?></td>

    <td>
        <?php foreach ($view->userTeams[$m['id']] ?? [] as $t): ?>
            <span
                class="team-dot"
                title="<?= htmlspecialchars($t['name']) ?>"
                style="background-color: <?= htmlspecialchars($t['color']) ?>"
            >●</span>
        <?php endforeach; ?>
    </td>
		<td>
		<?= htmlspecialchars($m['role_in_team']) ?>
		</td>
    <td>
        <form method="post" action="<?= Url::current() ?>">
            <?= Csrf::getField(); ?>

            <!-- !!! DŮLEŽITÉ: membership_id -->
            <input type="hidden" name="change_user_role" value="<?= $m['membership_id'] ?>">

            <select name="role_in_team">
                <?php foreach ($rolesInTeam as $key => $label): ?>
                    <option value="<?= $key ?>"
                        <?= $key === $m['role_in_team'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Změnit roli</button>
        </form>
    </td>

    <td>
        <form method="post" action="<?= Url::current() ?>" style="display:inline">
            <?= Csrf::getField(); ?>
            <input type="hidden" name="remove_membership_id" value="<?= $m['membership_id'] ?>">
            <button type="submit">Odebrat</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<hr>

<!-- ===================== -->
<!-- PŘIDÁNÍ ČLENA -->
<!-- ===================== -->
<h3>Přidat člena</h3>

<table border="1" cellpadding="6">
<thead>
<tr>
    <th>Jméno</th>
    <th>Členem týmů</th>
    <th>Role</th>
    <th>Akce</th>
</tr>
</thead>

<?php foreach ($view->availableUsers as $u): ?>
<tr>
    <td><?= htmlspecialchars($u['last_name'] . ' ' . $u['first_name']) ?></td>

    <td>
        <?php foreach ($view->userTeams[$u['id']] ?? [] as $t): ?>
            <span
                class="team-dot"
                title="<?= htmlspecialchars($t['name']) ?>"
                style="background-color: <?= htmlspecialchars($t['color']) ?>"
            >●</span>
        <?php endforeach; ?>
    </td>

    <td>
        <form method="post" action="<?= Url::current() ?>">
            <?= Csrf::getField(); ?>
            <input type="hidden" name="add_user_id" value="<?= $u['id'] ?>">

            <select name="role_in_team">
                <?php foreach ($rolesInTeam as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $key === 'member' ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
    </td>

    <td>
            <button type="submit">Přidat</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>
