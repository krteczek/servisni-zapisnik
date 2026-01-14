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
        <?php foreach ((array)$messages as $message): ?>
            <li><?= htmlspecialchars($message) ?></li>
        <?php endforeach ?>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post"
      action="<?= Url::to('/users') ?>"
      autocomplete="off"
      data-lpignore="true">

    <?= $this->csrfField() ?>

    <input type="text" name="username" autocomplete="username" hidden>
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
            <option value="<?= $key ?>" <?= $key === ($old['global_role'] ?? '') ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach ?>
    </select><br><br>

    <button type="submit">Vytvořit</button>
</form>