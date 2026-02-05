<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Roles;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$old    = $view->data ?? [];
$errors = $view->errors ?? [];
?>
<p>„Uživatel po vytvoření účtu obdrží aktivační e-mail, s odkazem, pomocí kterého si nastaví heslo a dokončí vytvoření svého účtu.“</p>
<?php if ($errors): ?>
<ul class="errors">
    <?php foreach ($errors as $field => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            <li><?= e($message) ?></li>
        <?php endforeach ?>
    <?php endforeach ?>
</ul>
<?php endif ?>
<br>
<form method="post"
      autocomplete="off"
      data-lpignore="true">

    <?= Csrf::getField() ?>

    <label>Email *</label><br>
    <input type="email"
           name="email"
           value="<?= e($old['email'] ?? '') ?>"><br><br>

    <label>Číslo zaměstnance *</label><br>
    <input type="text"
           name="employee_number"
           value="<?= e($old['employee_number'] ?? '') ?>"><br><br>

    <label>Jméno *</label><br>
    <input type="text"
           name="first_name"
           value="<?= e($old['first_name'] ?? '') ?>"><br><br>

    <label>Příjmení *</label><br>
    <input type="text"
           name="last_name"
           value="<?= e($old['last_name'] ?? '') ?>"><br><br>

    <label>Role</label><br>
    <select name="global_role">
        <?php //var_dump($view->roles);
foreach ($view->roles as $key => $label): ?>
<option value="<?= e($key) ?>"
    <?= $key === ($old['global_role'] ?? Roles::default()) ? 'selected' : '' ?>>
    <?= e($label) ?>
</option>        <?php endforeach ?>
    </select><br><br>

    <button type="submit">Vytvořit</button>
</form>
<p>
    <a href="<?= Url::to('/users') ?>" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>
<?php require __DIR__ . '/../layout/footer.php'; ?>