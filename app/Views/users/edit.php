<?php
declare(strict_types=1);

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$old    = $view->old ?? [];
$errors = $view->errors ?? [];
?>

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

    <?= $this->csrfField() ?>

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
        <?php foreach ($view->roles as $key => $label): ?>
            <option value="<?= e($key) ?>"
                <?= $key === ($old['global_role'] ?? '') ? 'selected' : '' ?>>
                <?= e($label) ?>
            </option>
        <?php endforeach ?>
    </select><br><br>

    <label>
        <input type="checkbox" name="active" <?= !empty($old['active']) ? 'checked' : '' ?>>
        Aktivní účet
    </label><br><br>

    <button type="submit">Uložit změny</button>
</form>