<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$data   = $view->data;
$errors = $view->errors;

$account = $data['account'] ?? [];

?>

<div class="create-container">

    <div class="card">

        <div class="ui-alert ui-alert-info">
            <strong>Informace:</strong>
            Zde můžete přidat bankovní účet vlastní firmy.
            Účet lze později upravit, aktivovat, deaktivovat nebo nastavit
            jako výchozí.
        </div>

        <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

        <div class="card-body">

            <div class="form-container">

                <form method="post"
                      class="form company-bank-account-form"
                      autocomplete="off">

                    <?= Csrf::getField() ?>

                    <fieldset>

                        <legend>Bankovní účet</legend>

                        <div class="form-group <?= isset($errors['name']) ? 'has-error' : '' ?>">

                            <label for="name">
                                Název účtu
                            </label>

                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                value="<?= e($account['name'] ?? '') ?>"
                                maxlength="100"
                                required
                            >

                            <small>
                                Například „Běžný účet“, „Provozní účet“ nebo
                                „Spořicí účet“.
                            </small>

                            <?php if (isset($errors['name'])): ?>
                                <span class="error-message">
                                    <?= e($errors['name'][0]) ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="form-group <?= isset($errors['account_prefix']) ? 'has-error' : '' ?>">

                            <label for="account_prefix">
                                Předčíslí účtu
                            </label>

                            <input
                                type="text"
                                name="account_prefix"
                                id="account_prefix"
                                class="form-control"
                                value="<?= e($account['account_prefix'] ?? '') ?>"
                                maxlength="6"
                            >

                            <?php if (isset($errors['account_prefix'])): ?>
                                <span class="error-message">
                                    <?= e($errors['account_prefix'][0]) ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="form-group <?= isset($errors['account_number']) ? 'has-error' : '' ?>">

                            <label for="account_number">
                                Číslo účtu
                            </label>

                            <input
                                type="text"
                                name="account_number"
                                id="account_number"
                                class="form-control"
                                value="<?= e($account['account_number'] ?? '') ?>"
                                maxlength="20"
                                required
                            >

                            <?php if (isset($errors['account_number'])): ?>
                                <span class="error-message">
                                    <?= e($errors['account_number'][0]) ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="form-group <?= isset($errors['bank_code']) ? 'has-error' : '' ?>">

                            <label for="bank_code">
                                Kód banky
                            </label>

                            <input
                                type="text"
                                name="bank_code"
                                id="bank_code"
                                class="form-control"
                                value="<?= e($account['bank_code'] ?? '') ?>"
                                maxlength="4"
                            >

                            <?php if (isset($errors['bank_code'])): ?>
                                <span class="error-message">
                                    <?= e($errors['bank_code'][0]) ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="form-group <?= isset($errors['iban']) ? 'has-error' : '' ?>">

                            <label for="iban">
                                IBAN
                            </label>

                            <input
                                type="text"
                                name="iban"
                                id="iban"
                                class="form-control"
                                value="<?= e($account['iban'] ?? '') ?>"
                                maxlength="34"
                            >

                            <?php if (isset($errors['iban'])): ?>
                                <span class="error-message">
                                    <?= e($errors['iban'][0]) ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="form-group <?= isset($errors['bic']) ? 'has-error' : '' ?>">

                            <label for="bic">
                                BIC/SWIFT
                            </label>

                            <input
                                type="text"
                                name="bic"
                                id="bic"
                                class="form-control"
                                value="<?= e($account['bic'] ?? '') ?>"
                                maxlength="11"
                            >

                            <?php if (isset($errors['bic'])): ?>
                                <span class="error-message">
                                    <?= e($errors['bic'][0]) ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="form-group">

                            <label>
                                <input
                                    type="checkbox"
                                    name="is_default"
                                    value="1"
                                    <?= !empty($account['is_default']) ? 'checked' : '' ?>
                                >

                                Nastavit jako výchozí účet
                            </label>

                        </div>

                    </fieldset>

                    <div class="form-actions">

                        <button type="submit" class="btn btn-primary">
                            <span class="btn-icon"></span>
                            Uložit bankovní účet
                        </button>

                        <a
                            href="<?= Url::to(
                                '/{tenant}/system/company/bank-accounts/list'
                            ) ?>/#main"
                            class="btn btn-secondary"
                        >
                            <span class="btn-icon">←</span>
                            Zpět
                        </a>

                    </div>

                </form>

            </div>

        </div>
    </div>

    <div class="card card-help" id="helpCard">

        <div class="card-body">

            <details>
                <summary>Informace pro administrátory</summary>

                <p>
                    Bankovní účet lze kdykoliv upravit. Deaktivovaný účet
                    zůstává v systému a lze jej později znovu aktivovat.
                </p>

                <ul>
                    <li>
                        Název účtu slouží pro rozlišení účtů v aplikaci.
                    </li>
                    <li>
                        Jako výchozí může být nastaven pouze aktivní účet.
                    </li>
                    <li>
                        Účet se nemaže, pouze deaktivuje.
                    </li>
                    <li>
                        Bankovní údaje se později při vystavení faktury
                        ukládají do jejího historického snapshotu.
                    </li>
                </ul>

            </details>

        </div>

    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>