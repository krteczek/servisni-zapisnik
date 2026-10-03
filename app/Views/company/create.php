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
$ares    = $data['ares'] ?? null;

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
                        <legend>Údaje firmy</legend>

                        <div class="form-group">

                            <label for="ico">IČO</label>

                            <input
                                type="text"
                                name="ico"
                                id="ico"
                                value="<?= e($company['ico'] ?? '') ?>"
                                readonly
                            >

                            <button
                                type="button"
                                data-ajax-loader
                                data-ajax-source="ico"
                                data-ajax-url="<?= Url::to('/{tenant}/ajax/company/ares/ico') ?>"
                                data-ajax-message="ares-message"
                            >
                                Načíst data z ARES
                            </button>

                            <div id="ares-message" hidden></div>

                        </div>

                        

<div class="company-ares-data" hidden>

    <h3>Údaje z ARES</h3>

    <dl>
        <dt>Jméno / název</dt>
        <dd data-ajax-field="officialName"></dd>

        <dt>DIČ</dt>
        <dd data-ajax-field="dic"></dd>

        <dt>Ulice</dt>
        <dd data-ajax-field="street"></dd>

        <dt>Číslo domu</dt>
        <dd data-ajax-field="houseNumber"></dd>

        <dt>Číslo orientační</dt>
        <dd data-ajax-field="orientationNumber"></dd>

        <dt>Část obce</dt>
        <dd data-ajax-field="cityPart"></dd>

        <dt>Obec</dt>
        <dd data-ajax-field="city"></dd>

        <dt>PSČ</dt>
        <dd data-ajax-field="postalCode"></dd>

        <dt>Stát</dt>
        <dd data-ajax-field="countryCode"></dd>

        <dt>Doručovací adresa</dt>
        <dd>
            <span data-ajax-field="deliveryAddress1"></span><br>
            <span data-ajax-field="deliveryAddress2"></span><br>
            <span data-ajax-field="deliveryAddress3"></span>
        </dd>
    </dl>

</div>

                

                        <div class="form-group <?= isset($errors['trade_name']) ? 'has-error' : '' ?>"></div>
         <div style="border-top: 1px solid #d6dbe3; padding-top: 10px; margin-top: 10px;">
                        <label for="trade_name">
                                Obchodní název
                            </label>

                            <input
                                type="text"
                                name="trade_name"
                                id="trade_name"
                                class="form-control"
                                value="<?= e($details['trade_name'] ?? '') ?>"
                                maxlength="255"
                            >

                            <small>
                                Pokud Vaše společnost používá nějaký obchodní název,
                                zde ho uveďte. Bude se také propisovat do vašich faktur.
                            </small>

                            <?php if (isset($errors['trade_name'])): ?>
                                <span class="error-message">
                                    <?= e($errors['trade_name'][0]) ?>
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
                    Identifikační a adresní údaje firmy jsou načítány z ARES.
                    V aplikaci se tyto údaje neupravují ručně.
                </p>

                <ul>
                    <li>IČO je převzato z údajů firemního účtu a zde se nemění.</li>
                    <li>Údaje firmy jsou načteny z ARES.</li>
                    <li>Obchodní název je nepovinný a lze jej zadat samostatně.</li>
                    <li>Obchodní název se může zobrazovat na vystavených fakturách.</li>
                </ul>

            </details>

        </div>

    </div>

</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>