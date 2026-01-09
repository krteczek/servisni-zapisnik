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
    <?php foreach ($errors as $msg): ?>
        <li><?= htmlspecialchars($msg, ENT_QUOTES) ?></li>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post" action="<?= Url::to('/users') ?>">
    <?= Csrf::getField() ?>

    <label>Email<br>
        <input type="email" name="email"
               value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>" required>
    </label><br><br>

    <label>Číslo zaměstnance<br>
        <input name="employee_number"
               value="<?= htmlspecialchars($old['employee_number'] ?? '', ENT_QUOTES) ?>">
    </label><br><br>

    <label>Jméno<br>
        <input name="first_name"
               value="<?= htmlspecialchars($old['first_name'] ?? '', ENT_QUOTES) ?>">
    </label><br><br>

    <label>Příjmení<br>
        <input name="last_name"
               value="<?= htmlspecialchars($old['last_name'] ?? '', ENT_QUOTES) ?>">
    </label><br><br>

    <label>Heslo<br>
        <input type="password" name="password">
    </label><br><br>

    <label>Role<br>
        <select name="global_role">
            <?php foreach ($view->roles as $key => $label): ?>
                <option value="<?= $key ?>" <?= $key === ($view->selectedRole ?? '') ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label, ENT_QUOTES) ?>
                </option>
            <?php endforeach ?>
        </select>
    </label><br><br>

    <button type="submit">Vytvořit</button>
</form>
