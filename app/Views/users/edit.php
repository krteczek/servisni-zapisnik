<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Roles;
use App\Core\Csrf;
use App\Core\UserGuard;

$css = '';
//require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

$old    = $view->old ?? [];
$errors = $view->errors ?? [];

$fullName = trim(
    ($old['first_name'] ?? '') . ' ' . ($old['last_name'] ?? '')
);
?>
<?= $css ?>

<div class="ui-alert ui-alert-warning">
    <strong>Upozornění:</strong>
    Uživatelé označení jako neaktivní se nemohou přihlásit.
</div>

<div class="user-edit-wrapper">

    <h2 class="user-edit-title">
        Změna údajů uživatele
        <span><?= e($fullName ?: '—') ?></span>
    </h2>

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

        <!-- anti password manager -->
        <input type="text" autocomplete="username" hidden>
        <input type="password" autocomplete="current-password" hidden>

        <!-- EMAIL -->
        <div class="form-group">
            <label>Email</label>

            <?php if (!UserGuard::isProtected($old)): ?>
                <input name="email"
                       value="<?= e($old['email'] ?? '') ?>">
            <?php else: ?>
                <div class="form-static">
                    <?= e($old['email'] ?? '') ?>
                    <small>Email u tohoto účtu nelze změnit.</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- EMPLOYEE NUMBER -->
        <div class="form-group">
            <label>Číslo zaměstnance</label>
            <input name="employee_number"
                   value="<?= e($old['employee_number'] ?? '') ?>">
        </div>

        <!-- FIRST NAME -->
        <div class="form-group">
            <label>Jméno</label>
            <input name="first_name"
                   value="<?= e($old['first_name'] ?? '') ?>">
        </div>

        <!-- LAST NAME -->
        <div class="form-group">
            <label>Příjmení</label>
            <input name="last_name"
                   value="<?= e($old['last_name'] ?? '') ?>">
        </div>

        <!-- ROLE + ACTIVE -->
        <div class="form-group">

            <?php if (!UserGuard::isProtected($old)): ?>

                <label>Role</label>
                <select name="global_role">
                    <?php foreach ($view->roles as $key => $label): ?>
                        <option value="<?= e($key) ?>"
                            <?= $key === ($old['global_role'] ?? Roles::default()) ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach ?>
                </select>

                <label class="checkbox">
                    <input type="checkbox"
                           name="active"
                           <?= !empty($old['active']) ? 'checked' : '' ?>>
                    Aktivní účet
                </label>

            <?php else: ?>

                <div class="form-static">
                    <strong>Role:</strong>
                    <?= e($old['global_role'] ?? '') ?><br>
                    <small>Tento účet nelze upravovat ani deaktivovat.</small>
                </div>

            <?php endif; ?>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Uložit změny
            </button>

            <a href="<?= Url::to('/{tenant}/users') ?>/#main"
               class="btn-secondary">
                ← Zpět na výpis uživatelů
            </a>
        </div>

    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
