<?php
declare(strict_types=1);

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/submenu.php';

use App\Core\Url;
use App\Core\Csrf;

$old = $view->old ?? [];
$errors = $view->errors ?? [];
?>

<h1>Nový uživatel</h1>

<?php if ($errors): ?>
    <ul style="color:red">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach ?>
    </ul>
<?php endif ?>

<form method="post" action="<?= Url::to('/users') ?>">
    <?= Csrf::getField() ?>

    <label>Email</label><br>
    <input type="email" name="email"
           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required><br><br>

    <label>Jméno</label><br>
    <input type="text" name="first_name"
           value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"><br><br>

    <label>Příjmení</label><br>
    <input type="text" name="last_name"
           value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"><br><br>

    <label>Číslo zaměstnance</label><br>
    <input type="text" name="employee_number"
           value="<?= htmlspecialchars($old['employee_number'] ?? '') ?>"><br><br>

    <label>Heslo</label><br>
    <input type="password" name="password"><br><br>

    <label>Role</label><br>
    <select name="role">
        <?php foreach ($view->roles as $key => $label): ?>
            <option value="<?= $key ?>"
                <?= $key === ($view->selectedRole ?? '') ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach ?>
    </select><br><br>

    <button type="submit">Vytvořit</button>
</form>
