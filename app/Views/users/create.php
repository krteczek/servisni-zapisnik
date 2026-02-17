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
$roles  = $view->roles ?? []; // předpokládám, že roles jsou v $view
?>

<div class="user-create-container">

    <!-- INFO ALERT (stejný styl jako v report šabloně) -->
    <div class="ui-alert ui-alert-info">
        <strong>Informace:</strong>
        Uživatel po vytvoření účtu obdrží aktivační e-mail,
        pomocí kterého si nastaví heslo a dokončí vytvoření účtu.
    </div>

    <!-- Zobrazení chyb (stejné jako v report šabloně) -->
    <?php if (!empty($errors)): ?>
        <div class="ui-alert ui-alert-danger">
            <ul>
                <?php foreach ($errors as $field => $error): ?>
                    <?php if (is_array($error)): ?>
                        <?php foreach ($error as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><?= e($error) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- HLAVNÍ FORMULÁŘ -->
    <div class="form-container">
        <form method="post" 
              class="form user-form" 
              autocomplete="off" 
              data-lpignore="true">

            <?= Csrf::getField() ?>

            <!-- EMAIL -->
            <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                <label for="email">Email <span class="req">*</span></label>
                <input type="email"
                       name="email"
                       id="email"
                       class="form-control"
                       value="<?= e($old['email'] ?? '') ?>"
                       placeholder="napr. jan.novak@firma.cz"
                       required>
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <!-- ČÍSLO ZAMĚSTNANCE -->
            <div class="form-group <?= isset($errors['employee_number']) ? 'has-error' : '' ?>">
                <label for="employee_number">Číslo zaměstnance <span class="req">*</span></label>
                <input type="text"
                       name="employee_number"
                       id="employee_number"
                       class="form-control"
                       value="<?= e($old['employee_number'] ?? '') ?>"
                       placeholder="napr. 12345"
                       required>
                <?php if (isset($errors['employee_number'])): ?>
                    <span class="error-message"><?= e($errors['employee_number']) ?></span>
                <?php endif; ?>
            </div>

            <!-- JMÉNO A PŘÍJMENÍ VE DVOU SLOUPCÍCH -->
            <div class="form-row">
                <div class="form-group <?= isset($errors['first_name']) ? 'has-error' : '' ?>">
                    <label for="first_name">Jméno <span class="req">*</span></label>
                    <input type="text"
                           name="first_name"
                           id="first_name"
                           class="form-control"
                           value="<?= e($old['first_name'] ?? '') ?>"
                           required>
                    <?php if (isset($errors['first_name'])): ?>
                        <span class="error-message"><?= e($errors['first_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['last_name']) ? 'has-error' : '' ?>">
                    <label for="last_name">Příjmení <span class="req">*</span></label>
                    <input type="text"
                           name="last_name"
                           id="last_name"
                           class="form-control"
                           value="<?= e($old['last_name'] ?? '') ?>"
                           required>
                    <?php if (isset($errors['last_name'])): ?>
                        <span class="error-message"><?= e($errors['last_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ROLE -->
            <div class="form-group <?= isset($errors['global_role']) ? 'has-error' : '' ?>">
                <label for="global_role">Role <span class="req">*</span></label>
                <select name="global_role" id="global_role" class="form-control">
                    <?php foreach ($roles as $key => $label): ?>
                        <option value="<?= e($key) ?>"
                            <?= ($key === ($old['global_role'] ?? Roles::default())) ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['global_role'])): ?>
                    <span class="error-message"><?= e($errors['global_role']) ?></span>
                <?php endif; ?>
            </div>

            <!-- FORMULÁŘOVÉ TLAČÍTKA -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">➕</span>
                    Vytvořit uživatele
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

<!-- JavaScript pro případné interakce (klidně můžeš přidat validaci atd.) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Např. automatické generování loginu z emailu? Nebo cokoliv jiného
    const emailInput = document.getElementById('email');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    
    // Můžeš přidat nějaké hezké chování, třeba automatické převádění na lowercase
    // nebo cokoliv, co ti usnadní práci
});
</script>


<?php require __DIR__ . '/../layout/footer.php'; ?>