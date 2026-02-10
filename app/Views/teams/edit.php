<?php
declare(strict_types=1);

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$team        = $view->team;
$rolesInTeam = $view->rolesInTeam;
?>
<?= $css ?>
<!-- ===================== -->
<!-- ÚPRAVA TÝMU -->
<!-- ===================== -->
<form method="post" action="<?= Url::current() ?>">
    <?= $this->csrfField() ?>

    <label>Název</label><br>
    <input type="text" name="name" value="<?= e($team['name']) ?>"><br><br>

    <label>Barva</label><br>
    <input type="color" name="color" value="<?= e($team['color']) ?>"><br><br>

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
    <th>Role</th>
    <th>Změna role</th>
    <th>Akce</th>
</tr>
</thead>

<tbody>
<?php foreach ($view->members as $m): ?>
<tr>
    <td><?= e($m['last_name'] . ' ' . $m['first_name']) ?></td>

    <td>
        <?php foreach ($view->userTeams[$m['id']] ?? [] as $t): ?>
            <span class="team-dot"
                  title="<?= e($t['name']) ?>"
                  style="background-color: <?= e($t['color']) ?>">
                ●
            </span>
        <?php endforeach ?>
    </td>

    <td><?= e($m['role_in_team']) ?></td>

    <td>
        <form method="post" action="<?= Url::current() ?>">
            <?= $this->csrfField() ?>

            <input type="hidden"
                   name="change_user_role"
                   value="<?= (int) $m['membership_id'] ?>">

            <select name="role_in_team">
                <?php foreach ($rolesInTeam as $key => $label): ?>
                    <option value="<?= e($key) ?>"
                        <?= $key === $m['role_in_team'] ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach ?>
            </select>

            <button type="submit">Změnit</button>
        </form>
    </td>

    <td>
        <form method="post" action="<?= Url::current() ?>">
            <?= $this->csrfField() ?>
            <input type="hidden"
                   name="remove_membership_id"
                   value="<?= (int) $m['membership_id'] ?>">
            <button type="submit">Odebrat</button>
        </form>
    </td>
</tr>
<?php endforeach ?>
</tbody>
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

<tbody>
<?php foreach ($view->availableUsers as $u): ?>
<?php if($u['global_role'] === 'root') {
	continue;
}
?>
<tr>
    <td><?= e($u['last_name'] . ' ' . $u['first_name']) ?></td>

    <td>
        <?php foreach ($view->userTeams[$u['id']] ?? [] as $t): ?>
            <span class="team-dot"
                  title="<?= e($t['name']) ?>"
                  style="background-color: <?= e($t['color']) ?>">
                ●
            </span>
        <?php endforeach ?>
    </td>

    <td>
        <form method="post" action="<?= Url::current() ?>">
            <?= $this->csrfField() ?>
            <input type="hidden" name="add_user_id" value="<?= (int) $u['id'] ?>">

            <select name="role_in_team">
                <?php foreach ($rolesInTeam as $key => $label): ?>
                    <option value="<?= e($key) ?>"
                        <?= $key === 'member' ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach ?>
            </select>
    </td>

    <td>
            <button type="submit">Přidat</button>
        </form>
    </td>
</tr>
<?php endforeach ?>
</tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>