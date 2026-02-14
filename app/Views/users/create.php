<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Roles;
use App\Core\Csrf;


$css = '';
require __DIR__ . '/style.php';

require __DIR__ . '/../layout/header.php';

$old    = $view->data ?? [];
$errors = $view->errors ?? [];
?>
<?= $css ?>
<div class="user-edit-wrapper">

    <div class="ui-alert ui-alert-warning">
        <strong>Informace:</strong>
        Uživatel po vytvoření účtu obdrží aktivační e-mail,
        pomocí kterého si nastaví heslo a dokončí vytvoření účtu.
    </div>

    <?php if ($errors): ?>
        <div class="ui-alert ui-alert-error">
            <strong>Formulář obsahuje chyby:</strong>
            <ul>
                <?php foreach ($errors as $messages): ?>
                    <?php foreach ((array)$messages as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach ?>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form method="post"
          class="user-form"
          autocomplete="off"
          data-lpignore="true">

        <?= Csrf::getField() ?>

        <!-- EMAIL -->
        <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input type="email"
                   name="email"
                   value="<?= e($old['email'] ?? '') ?>">
        </div>

        <!-- EMPLOYEE NUMBER -->
        <div class="form-group">
            <label>Číslo zaměstnance <span class="req">*</span></label>
            <input type="text"
                   name="employee_number"
                   value="<?= e($old['employee_number'] ?? '') ?>">
        </div>

        <!-- FIRST NAME -->
        <div class="form-group">
            <label>Jméno <span class="req">*</span></label>
            <input type="text"
                   name="first_name"
                   value="<?= e($old['first_name'] ?? '') ?>">
        </div>

        <!-- LAST NAME -->
        <div class="form-group">
            <label>Příjmení <span class="req">*</span></label>
            <input type="text"
                   name="last_name"
                   value="<?= e($old['last_name'] ?? '') ?>">
        </div>

        <!-- ROLE -->
        <div class="form-group">
            <label>Role</label>
            <select name="global_role">
                <?php foreach ($view->roles as $key => $label): ?>
                    <option value="<?= e($key) ?>"
                        <?= $key === ($old['global_role'] ?? Roles::default()) ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Vytvořit uživatele
            </button>

            <a href="<?= Url::to('/{tenant}/users') ?>/#main"
               class="btn-secondary">
                ← Zpět na výpis uživatelů
            </a>
        </div>

    </form>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
