<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Csrf;

$css = '';
$team        = $view->team;
$rolesInTeam = $view->rolesInTeam;

require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

?>

<?php if (!empty($view->error)): ?>
    <p style="color:#c62828; max-width:900px; margin:1rem auto;">
        <?= htmlspecialchars($view->errors) ?>
    </p>
<?php endif; ?>





<div class="team-intro">
<p>
    Na této stránce spravuješ nastavení týmu.
</p>
<ul>
    <li>upravíš název a barvu týmu,</li>
    <li>nastavíš, kdo v týmu je a jakou má roli,</li>
    <li>můžeš přidávat nebo odebírat lidi (jeden člověk může být ve více týmech).</li>
</ul>
</div>

<!-- ===================== -->
<!-- ÚPRAVA TÝMU -->
<!-- ===================== -->
<form method="post" action="<?= Url::current() ?>">
    <?= Csrf::getField() ?>

    <table class="form-table">
        <tr>
            <th>
                <label for="name">Název týmu <span class="req">*</span></label>
            </th>
            <td>
                <input
                    id="name"
                    name="name"
                    required
                    value="<?= e($team['name']) ?>"
                >
            </td>
        </tr>

        <tr>
            <th>
                <label for="color">Barva týmu</label>
            </th>
            <td>
                <input
                    id="color"
                    type="color"
                    name="color"
                    value="<?= e($team['color']) ?>"
                    style="height:38px; padding:2px;"
                >
            </td>
        </tr>

        <tr>
            <th></th>
            <td class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Uložit tým
                </button>
            </td>
        </tr>
    </table>
</form>

<hr>
<br>
<!-- ===================== -->
<!-- ČLENOVÉ TÝMU -->
<!-- ===================== -->

<h2 id="userlist">Členové týmu</h2>
<p class="hint">
    Změň roli člena týmu pomocí rozbalovací nabídky.
    Přidání nebo odebrání člověka provedeš kliknutím na šipky.
</p>

<div class="user-dual-list">

    <!-- V TÝMU -->
    <div class="user-list">
        <h3>V týmu</h3>

        <?php if (empty($view->members)): ?>
            <div class="empty-list">V tomhle týmu nikdo není</div>
        <?php endif; ?>

        <?php foreach ($view->members as $m): ?>
        <div class="user-row">
    <form method="post" action="<?= Url::current() ?>#main" class="inline-form">
        <?= Csrf::getField() ?>

        <input type="hidden"
               name="change_user_role"
               value="<?= (int) $m['membership_id'] ?>">

        <select name="role_in_team" onchange="this.form.submit()">
            <?php foreach ($rolesInTeam as $key => $label): ?>
                <option value="<?= e($key) ?>"
                    <?= $key === $m['role_in_team'] ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach ?>
        </select>
    </form>

    <span class="user-name"><?= e($m['last_name'].' '.$m['first_name']) ?></span>

    <form method="post" action="<?= Url::current() ?>#main" class="inline-form remove-form">
        <?= Csrf::getField() ?>
        <input type="hidden" name="remove_membership_id" value="<?= (int) $m['membership_id'] ?>">
        <button class="btn danger"> → </button>
    </form>
</div>

        <?php endforeach ?>
    </div>

    <!-- K DISPOZICI -->
    <div class="user-list">
        <h3>K dispozici</h3>

        <?php if (empty($view->availableUsers)): ?>
            <div class="empty-list">Žádní další uživatelé</div>
        <?php endif; ?>

        <?php foreach ($view->availableUsers as $u): ?>
            <form method="post" class="user-row">
                <?= Csrf::getField() ?>
                <input type="hidden"
                       name="add_user_id"
                       value="<?= (int)$u['id'] ?>">
                <input type="hidden"
                       name="role_in_team"
                       value="member">

                <button class="btn btn-sm btn-secondary">
                    ←
                </button>

                <span class="user-name">
                    <?= e($u['last_name'].' '.$u['first_name']) ?>
                </span>
            </form>
        <?php endforeach ?>
    </div>
<p>
    <a href="<?= Url::to('/{tenant}/teams') ?>/#main" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>
</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>