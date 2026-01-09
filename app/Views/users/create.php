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

<form method="post"
      action="<?= Url::to('/users') ?>"
      autocomplete="off"
      data-lpignore="true">

    <?= Csrf::getField() ?>

    <!-- fake username (pro password managery) -->
    <input type="text" name="username" autocomplete="username" hidden>

    <!-- fake current password -->
    <input type="password" autocomplete="current-password" hidden>

    <label>Email</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"><br><br>

    <label>Číslo zaměstnance</label><br>
    <input type="text" name="employee_number" value="<?= htmlspecialchars($old['employee_number'] ?? '') ?>"><br><br>

    <label>Jméno</label><br>
    <input type="text" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"><br><br>

    <label>Příjmení</label><br>
    <input type="text" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"><br><br>

    <label>Heslo</label><br>
    <input type="password"
           name="new_password"
           autocomplete="new-password"
           data-lpignore="true"><br><br>

    <label>Potvrzení hesla</label><br>
    <input type="password"
           name="new_password_confirm"
           autocomplete="new-password"
           data-lpignore="true"><br><br>

    <label>Role</label><br>
    <select name="global_role">
        <?php foreach ($view->roles as $key => $label): ?>
            <option value="<?= $key ?>" <?= $key === ($view->selectedRole ?? '') ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach ?>
    </select><br><br>

    <button type="submit">Vytvořit</button>
</form>
