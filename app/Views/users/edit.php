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

<!-- ===============================
     SAME ALERT – už máš, jen nechám
     =============================== -->
<div class="ui-alert ui-alert-warning">
    <strong>Upozornění:</strong>
    Uživatelé označení jako neaktivní se nemohou přihlásit.
</div>

<!-- ===============================
     HLAVNÍ KONTEJNER (grid 2:1)
     =============================== -->
<div class="create-container">

    <!-- LEVÝ SLOUPEC – FORMULÁŘ -->
    <div class="card form-card">
        <div class="card-body">
            <h2 class="user-edit-title" style="margin-top:0; margin-bottom:1.5rem;">
                Změna údajů uživatele
                <span style="display:block; font-size:1.2rem; color:#4A6FA5;">
                    <?= e($fullName ?: '—') ?>
                </span>
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

                <!-- anti password manager (neškodí) -->
                <input type="text" autocomplete="username" hidden>
                <input type="password" autocomplete="current-password" hidden>

                <!-- EMAIL -->
                <div class="form-group">
                    <label for="email">Email</label>

                    <?php if (!UserGuard::isProtected($old)): ?>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control"
                               value="<?= e($old['email'] ?? '') ?>"
                               placeholder="jan.novak@firma.cz">
                    <?php else: ?>
                        <div class="form-static" style="padding:10px 12px; background:#f3f4f6; border-radius:8px;">
                            <?= e($old['email'] ?? '') ?>
                            <small style="display:block; color:#6c757d;">Email u tohoto účtu nelze změnit.</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- EMPLOYEE NUMBER -->
                <div class="form-group">
                    <label for="employee_number">Číslo zaměstnance</label>
                    <input type="text"
                           name="employee_number"
                           id="employee_number"
                           class="form-control"
                           value="<?= e($old['employee_number'] ?? '') ?>"
                           placeholder="např. 12345">
                </div>

<!-- TELEFON (stejně jako u create) -->
<div class="form-group">
    <label for="telefon">Telefonní číslo</label>
    <input type="text"
           name="telefon"
           id="telefon"
           class="form-control"
           value="<?= e($old['telefon'] ?? '') ?>"
           placeholder="např. +420 604 123 456">
    <small style="color: #6c757d; display: block; margin-top: 4px;">
        Nepovinné, pro rychlé kontaktování.
    </small>
</div>

                <!-- FIRST NAME + LAST NAME (form-row) -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Jméno</label>
                        <input type="text"
                               name="first_name"
                               id="first_name"
                               class="form-control"
                               value="<?= e($old['first_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Příjmení</label>
                        <input type="text"
                               name="last_name"
                               id="last_name"
                               class="form-control"
                               value="<?= e($old['last_name'] ?? '') ?>">
                    </div>
                </div>

                <!-- ROLE + ACTIVE -->
                <div class="form-group">
                    <?php if (!UserGuard::isProtected($old)): ?>

                        <label for="global_role">Role</label>
                        <select name="global_role" id="global_role" class="form-control">
                            <?php foreach ($view->roles as $key => $label): ?>
                                <option value="<?= e($key) ?>"
                                    <?= $key === ($old['global_role'] ?? Roles::default()) ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach ?>
                        </select>

                        <div style="margin-top:12px;">
                            <label class="checkbox" style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox"
                                       name="active"
                                       <?= !empty($old['active']) ? 'checked' : '' ?>
                                       style="width:auto;">
                                <span>Aktivní účet</span>
                            </label>
                        </div>

                    <?php else: ?>

                        <div class="form-static" style="padding:10px 12px; background:#f3f4f6; border-radius:8px;">
                            <strong>Role:</strong> <?= e($old['global_role'] ?? '') ?><br>
                            <small style="color:#6c757d;">Tento účet nelze upravovat ani deaktivovat.</small>
                        </div>

                    <?php endif; ?>
                </div>

                <!-- FORM ACTIONS (stejné jako u create) -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Uložit změny
                    </button>

                    <a href="<?= Url::to('/{tenant}/users') ?>/#main"
                       class="btn btn-secondary">
                        <span class="btn-icon">←</span>
                        Zpět na výpis uživatelů
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- PRAVÝ SLOUPEC – NÁPOVĚDA -->
    <div class="card card-help" id="helpCard">
        <div class="card-body">
            <h3>Nápověda k úpravě uživatele</h3>

            <h4>Chráněné účty</h4>
            <p>
                Některé účty (např. první admin, speciální systémové účty)
                lze upravovat jen částečně nebo vůbec. Chráníme tím funkčnost systému.
            </p>

            <h4>Změna emailu</h4>
            <p>
                Email lze změnit jen u běžných účtů. Po změně emailu musí
                uživatel při přihlášení do systému, použít nový email..
            </p>

            <h4>Deaktivace účtu</h4>
            <p>
                Neaktivní uživatel se nemůže přihlásit, ale zůstává
                zachován v historii úkolů a zakázek (lze ho kdykoli znovu obnovit).
            </p>

            <h4>Role</h4>
            <ul style="padding-left:1.2rem;">
                <li><strong>Admin</strong> – plný přístup</li>
                <li><strong>Mistr</strong> – spravuje party, zakázky, úkoly</li>
                <li><strong>Předák</strong> – koordinuje práci</li>
                <li><strong>Montér</strong> – reportuje splněné úkoly</li>
            </ul>
        </div>
    </div>

</div> <!-- /.create-container -->

<?php require __DIR__ . '/../layout/footer.php'; ?>