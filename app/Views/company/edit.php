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

<div class="create-container company-edit-container">

    <div class="card">

        <div class="ui-alert ui-alert-info">
            <strong>Informace:</strong>
            Zde můžete upravit obchodní název firmy a aktualizovat
            identifikační a adresní údaje z ARES.
        </div>

        <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

        <div class="card-body">

            <div class="form-container">

                <form method="post"
                      class="form company-form"
                      autocomplete="off">

                    <?= Csrf::getField() ?>

                    <fieldset>

                        <legend>Firemní údaje</legend>

                        <!-- IČO + ARES -->

                        <div class="form-group">

                            <label for="ico">IČO</label>

                            <div class="form-row">

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

                            </div>

                            <div id="ares-message" hidden></div>

                        </div>


                        <!-- POROVNÁNÍ ÚDAJŮ -->

                        <div
                            class="company-ares-data"
                            id="company-ares-comparison"
                            
                        >

                            <h3>Porovnání údajů</h3>

                            <p>
                                Vlevo jsou údaje aktuálně uložené v aplikaci.
                                Vpravo jsou údaje právě načtené z ARES.
                            </p>

                            <table class="data-table company-ares-table">

                                <thead>
                                    <tr>
                                        <th>Údaj</th>
                                        <th>Aktuálně v databázi</th>
                                        <th>ARES</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr>
                                        <th scope="row">Jméno / název</th>

                                        <td data-db-field="officialName">
                                            <?= e($details['official_name'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="officialName"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">DIČ</th>

                                        <td data-db-field="dic">
                                            <?= e($details['dic'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="dic"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">Ulice</th>

                                        <td data-db-field="street">
                                            <?= e($details['street'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="street"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">Číslo domu</th>

                                        <td data-db-field="houseNumber">
                                            <?= e($details['house_number'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="houseNumber"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">Číslo orientační</th>

                                        <td data-db-field="orientationNumber">
                                            <?= e($details['orientation_number'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="orientationNumber"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">Část obce</th>

                                        <td data-db-field="cityPart">
                                            <?= e($details['city_part'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="cityPart"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">Obec</th>

                                        <td data-db-field="city">
                                            <?= e($details['city'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="city"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">PSČ</th>

                                        <td data-db-field="postalCode">
                                            <?= e($details['postal_code'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="postalCode"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">Stát</th>

                                        <td data-db-field="countryCode">
                                            <?= e($details['country_code'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="countryCode"></td>
                                    </tr>

                                    <tr>
                                        <th scope="row">Doručovací adresa</th>

                                        <td data-db-field="deliveryAddress">
                                            <?= e($details['delivery_address_1'] ?? '') ?><br>
                                            <?= e($details['delivery_address_2'] ?? '') ?><br>
                                            <?= e($details['delivery_address_3'] ?? '') ?>
                                        </td>

                                        <td data-ajax-field="deliveryAddress">
                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>


                        <!-- OBCHODNÍ NÁZEV -->

                        <div
                            class="form-group <?= isset($errors['trade_name']) ? 'has-error' : '' ?>"
                            style="border-top: 1px solid #d6dbe3; padding-top: 10px; margin-top: 20px;"
                        >

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
                            Uložit obchodní název
                        </button>

                        <a
                            href="<?= Url::to('/{tenant}/system/company/detail') ?>/#main"
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
                    Identifikační a adresní údaje firmy jsou vedeny v databázi
                    aplikace. Pomocí tlačítka „Načíst data z ARES“ lze ověřit,
                    zda jsou údaje v ARES aktuální.
                </p>

                <ul>
                    <li>IČO je převzato z údajů firemního účtu a zde se nemění.</li>
                    <li>Údaje vlevo představují aktuální stav v databázi.</li>
                    <li>Údaje z ARES slouží k porovnání a případné aktualizaci.</li>
                    <li>Obchodní název je vlastní údaj firmy a lze jej upravit samostatně.</li>
                </ul>

            </details>

        </div>

    </div>

</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>