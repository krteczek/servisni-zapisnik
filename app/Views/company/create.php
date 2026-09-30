<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$data   = $view->data;
$errors = $view->errors;

$company = $data['company'] ?? [];
$details = $data['details'] ?? [];
?>

<div class="create-container">

    <div class="card">

        <div class="ui-alert ui-alert-info">
            <strong>Informace:</strong>
            Zde zadáte údaje vlastní firmy, které se budou používat například
            při vystavování faktur.
        </div>

        <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

        <div class="card-body">

            <div class="form-container">

                <form method="post"
                      class="form company-form"
                      autocomplete="off">

                    <?= Csrf::getField() ?>

                    <fieldset>
                        <legend>Základní údaje</legend>

                        <!-- IČO -->
                        <div class="form-row">

                            <div class="form-group">
                                <label for="ico">IČO</label>
                                <input type="text"
                                       id="ico"
                                       class="form-control"
                                       value="<?= e($company['ico'] ?? '') ?>"
                                       readonly>
                            </div>

                            <!-- OBCHODNÍ NÁZEV -->
                            <div class="form-group <?= isset($errors['official_name']) ? 'has-error' : '' ?>">
                                <label for="official_name">
                                    Obchodní název <span class="req">*</span>
                                </label>

                                <input type="text"
                                       name="official_name"
                                       id="official_name"
                                       class="form-control"
                                       value="<?= e($details['official_name'] ?? '') ?>"
                                       maxlength="255"
                                       required>

                                <?php if (isset($errors['official_name'])): ?>
                                    <span class="error-message">
                                        <?= e($errors['official_name'][0]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>

                        <!-- DIČ -->
                        <div class="form-group <?= isset($errors['dic']) ? 'has-error' : '' ?>">
                            <label for="dic">DIČ</label>

                            <input type="text"
                                   name="dic"
                                   id="dic"
                                   class="form-control"
                                   value="<?= e($details['dic'] ?? '') ?>"
                                   maxlength="20">

                            <?php if (isset($errors['dic'])): ?>
                                <span class="error-message">
                                    <?= e($errors['dic'][0]) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                    </fieldset>


                    <fieldset>
                        <legend>Adresa sídla</legend>

                        <!-- ULICE + ČÍSLA -->
                        <div class="form-row">

                            <div class="form-group <?= isset($errors['street']) ? 'has-error' : '' ?>">
                                <label for="street">Ulice</label>

                                <input type="text"
                                       name="street"
                                       id="street"
                                       class="form-control"
                                       value="<?= e($details['street'] ?? '') ?>"
                                       maxlength="255">

                                <?php if (isset($errors['street'])): ?>
                                    <span class="error-message">
                                        <?= e($errors['street'][0]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group <?= isset($errors['house_number']) ? 'has-error' : '' ?>">
                                <label for="house_number">Číslo domu</label>

                                <input type="text"
                                       name="house_number"
                                       id="house_number"
                                       class="form-control"
                                       value="<?= e($details['house_number'] ?? '') ?>"
                                       maxlength="20">

                                <?php if (isset($errors['house_number'])): ?>
                                    <span class="error-message">
                                        <?= e($errors['house_number'][0]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group <?= isset($errors['orientation_number']) ? 'has-error' : '' ?>">
                                <label for="orientation_number">Číslo orientační</label>

                                <input type="text"
                                       name="orientation_number"
                                       id="orientation_number"
                                       class="form-control"
                                       value="<?= e($details['orientation_number'] ?? '') ?>"
                                       maxlength="20">

                                <?php if (isset($errors['orientation_number'])): ?>
                                    <span class="error-message">
                                        <?= e($errors['orientation_number'][0]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>


                        <!-- ČÁST OBCE -->
                        <div class="form-group <?= isset($errors['city_part']) ? 'has-error' : '' ?>">
                            <label for="city_part">Část obce</label>

                            <input type="text"
                                   name="city_part"
                                   id="city_part"
                                   class="form-control"
                                   value="<?= e($details['city_part'] ?? '') ?>"
                                   maxlength="255">

                            <?php if (isset($errors['city_part'])): ?>
                                <span class="error-message">
                                    <?= e($errors['city_part'][0]) ?>
                                </span>
                            <?php endif; ?>
                        </div>


                        <!-- OBEC + PSČ + STÁT -->
                        <div class="form-row">

                            <div class="form-group <?= isset($errors['city']) ? 'has-error' : '' ?>">
                                <label for="city">
                                    Obec <span class="req">*</span>
                                </label>

                                <input type="text"
                                       name="city"
                                       id="city"
                                       class="form-control"
                                       value="<?= e($details['city'] ?? '') ?>"
                                       maxlength="255"
                                       required>

                                <?php if (isset($errors['city'])): ?>
                                    <span class="error-message">
                                        <?= e($errors['city'][0]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group <?= isset($errors['postal_code']) ? 'has-error' : '' ?>">
                                <label for="postal_code">
                                    PSČ <span class="req">*</span>
                                </label>

                                <input type="text"
                                       name="postal_code"
                                       id="postal_code"
                                       class="form-control"
                                       value="<?= e($details['postal_code'] ?? '') ?>"
                                       maxlength="10"
                                       required>

                                <?php if (isset($errors['postal_code'])): ?>
                                    <span class="error-message">
                                        <?= e($errors['postal_code'][0]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group <?= isset($errors['country_code']) ? 'has-error' : '' ?>">
                                <label for="country_code">Stát</label>

                                <input type="text"
                                       name="country_code"
                                       id="country_code"
                                       class="form-control"
                                       value="<?= e($details['country_code'] ?? 'CZ') ?>"
                                       maxlength="2">

                                <?php if (isset($errors['country_code'])): ?>
                                    <span class="error-message">
                                        <?= e($errors['country_code'][0]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>

                    </fieldset>


                    <fieldset>
                        <legend>Doručovací adresa</legend>

                        <div class="form-group <?= isset($errors['delivery_address_1']) ? 'has-error' : '' ?>">
                            <label for="delivery_address_1">Adresa – řádek 1</label>

                            <input type="text"
                                   name="delivery_address_1"
                                   id="delivery_address_1"
                                   class="form-control"
                                   value="<?= e($details['delivery_address_1'] ?? '') ?>"
                                   maxlength="255">

                            <?php if (isset($errors['delivery_address_1'])): ?>
                                <span class="error-message">
                                    <?= e($errors['delivery_address_1'][0]) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errors['delivery_address_2']) ? 'has-error' : '' ?>">
                            <label for="delivery_address_2">Adresa – řádek 2</label>

                            <input type="text"
                                   name="delivery_address_2"
                                   id="delivery_address_2"
                                   class="form-control"
                                   value="<?= e($details['delivery_address_2'] ?? '') ?>"
                                   maxlength="255">

                            <?php if (isset($errors['delivery_address_2'])): ?>
                                <span class="error-message">
                                    <?= e($errors['delivery_address_2'][0]) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errors['delivery_address_3']) ? 'has-error' : '' ?>">
                            <label for="delivery_address_3">Adresa – řádek 3</label>

                            <input type="text"
                                   name="delivery_address_3"
                                   id="delivery_address_3"
                                   class="form-control"
                                   value="<?= e($details['delivery_address_3'] ?? '') ?>"
                                   maxlength="255">

                            <?php if (isset($errors['delivery_address_3'])): ?>
                                <span class="error-message">
                                    <?= e($errors['delivery_address_3'][0]) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                    </fieldset>


                    <div class="form-actions">

                        <button type="submit" class="btn btn-primary">
                            <span class="btn-icon"></span>
                            Uložit firemní údaje
                        </button>

                        <a href="<?= Url::to('/{tenant}/system/company/detail') ?>/#main"
                           class="btn btn-secondary">
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
                    Tyto údaje představují vlastní identifikační a adresní údaje
                    firmy vedené v aplikaci.
                </p>

                <ul>
                    <li>IČO je převzato z údajů firemního účtu a zde se nemění.</li>
                    <li>Obchodní název je název vlastní firmy používaný v aplikaci.</li>
                    <li>DIČ je nepovinné.</li>
                    <li>Adresa sídla obsahuje údaje o oficiálním sídle firmy.</li>
                    <li>Doručovací adresa je nepovinná.</li>
                </ul>

            </details>

        </div>

    </div>

</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>