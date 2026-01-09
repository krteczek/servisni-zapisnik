<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$old    = $view->old ?? [];
$errors = $view->errors ?? [];
?>

<?php if ($errors): ?>
<ul style="color:red">
    <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post" action="<?= Url::to('/users/' . $old['id'] . '/edit') ?>">
    <?= Csrf::getField() ?>

    <label>Email</label><br>
    <input name="email" value="<?= htmlspecialchars($old['email']) ?>"><br><br>

    <label>Číslo zaměstnance</label><br>
    <input name="employee_number" value="<?= htmlspecialchars($old['employee_number']) ?>"><br><br>

    <label>Jméno</label><br>
    <input name="first_name" value="<?= htmlspecialchars($old['first_name']) ?>"><br><br>

    <label>Příjmení</label><br>
    <input name="last_name" value="<?= htmlspecialchars($old['last_name']) ?>"><br><br>

    <label>Role</label><br>
    <select name="global_role">
        <?php foreach ($view->roles as $key => $label): ?>
            <option value="<?= $key ?>" <?= $key === $old['global_role'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach ?>
    </select><br><br>

    <label>
        <input type="checkbox" name="active" <?= $old['active'] ? 'checked' : '' ?>>
        Aktivní účet
    </label><br><br>

    <button type="submit">Uložit změny</button>
</form>
