<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Roles;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$old    = $view->old ?? [];
$errors = $view->errors ?? [];
?>
<h2>Změna údajů uživatele:
    <?= htmlspecialchars($old['first_name'] ?? '') ?>
    <?= htmlspecialchars($old['last_name'] ?? '') ?>
</h2>

<?php if ($errors): ?>
<ul class="errors">
    <?php foreach ($errors as $field => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            <li><?= e($message) ?></li>
        <?php endforeach ?>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post"
      autocomplete="off"
      data-lpignore="true">

    <?= Csrf::getField() ?>

    <!-- anti password manager -->
    <input type="text" autocomplete="username" hidden>
    <input type="password" autocomplete="current-password" hidden>

    <label>Email</label><br>
    <input name="email" value="<?= e($old['email'] ?? '') ?>"><br><br>

    <label>Číslo zaměstnance</label><br>
    <input name="employee_number" value="<?= e($old['employee_number'] ?? '') ?>"><br><br>

    <label>Jméno</label><br>
    <input name="first_name" value="<?= e($old['first_name'] ?? '') ?>"><br><br>

    <label>Příjmení</label><br>
    <input name="last_name" value="<?= e($old['last_name'] ?? '') ?>"><br><br>

    <label>Role</label><br>
    <select name="global_role">
        <?php //var_dump($view->roles);
foreach ($view->roles as $key => $label): ?>
<option value="<?= e($key) ?>"
    <?= $key === ($old['global_role'] ?? Roles::default()) ? 'selected' : '' ?>>
    <?= e($label) ?>
</option>        <?php endforeach ?>
    </select><br><br>

    <label>
        <input type="checkbox" name="active" <?= !empty($old['active']) ? 'checked' : '' ?>>
        Aktivní účet
    </label><br><br>

    <button type="submit">Uložit změny</button>
</form>
<p>
    <a href="<?= Url::to('/users') ?>" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>
<?php require __DIR__ . '/../layout/footer.php'; ?>