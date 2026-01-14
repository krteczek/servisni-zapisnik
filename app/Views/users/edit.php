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
            <li><?= htmlspecialchars($message) ?></li>
        <?php endforeach ?>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post" action="<?= Url::to('/users/' . ($old['id'] ?? '') . '/edit') ?>">

    <?= $this->csrfField() ?>

    <label>Email</label><br>
    <input name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"><br><br>

    <label>Číslo zaměstnance</label><br>
    <input name="employee_number" value="<?= htmlspecialchars($old['employee_number'] ?? '') ?>"><br><br>

    <label>Jméno</label><br>
    <input name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"><br><br>

    <label>Příjmení</label><br>
    <input name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"><br><br>

    <label>Role</label><br>
    <select name="global_role">
        <?php foreach ($view->roles as $key => $label): ?>
            <option value="<?= $key ?>" <?= $key === ($old['global_role'] ?? '') ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach ?>
    </select><br><br>

    <label>
        <input type="checkbox" name="active" <?= !empty($old['active']) ? 'checked' : '' ?>>
        Aktivní účet
    </label><br><br>

    <button type="submit">Uložit změny</button>
</form>