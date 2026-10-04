<?php

declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$data   = $view->data;
$errors = $view->errors;

$id = (int) ($data['id'] ?? 0);
$isEdit = $id > 0;

?>

<div class="create-container">

    <div class="card">

        <div class="card-body">

            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

            <div class="form-container">

                <form
                    method="post"
                    class="form contact-form"
                    autocomplete="off"
                >

                    <?= Csrf::getField() ?>

                    <fieldset>

                        <legend>
                            <?= $isEdit ? 'Úprava zákazníka' : 'Nový zákazník' ?>
                        </legend>

                        <!-- =================================================
                             IČO + ARES
                        ================================================== -->

                        <div class="form-group">

                            <label for="ico">IČO</label>

                            <div class="form-row">

                                <input
                                    type="text"
                                    name="ico"
                                    id="ico"
                                    value="<?= e($data['ico'] ?? '') ?>"
                                    <?= $isEdit ? 'readonly' : '' ?>
                                >

                                <?php
                                if ($isEdit) {
                                    $aresPath =
                                        '/{tenant}/ajax/contact/' .
                                        $id .
                                        '/edit/ares/ico';
                                } else {
                                    $aresPath =
                                        '/{tenant}/ajax/contact/ares/ico';
                                }
                                ?>

                                <button
                                    type="button"
                                    data-ajax-loader
                                    data-ajax-source="ico"
                                    data-ajax-url="<?= Url::to($aresPath) ?>"
                                    data-ajax-message="ares-message"
                                >
                                    Načíst data z ARES
                                </button>

                            </div>

                            <div id="ares-message" hidden></div>

                        </div>


                        <?php if ($isEdit): ?>

                            <!-- =============================================
                                 ARES POROVNÁNÍ
                            ============================================== -->

                            <div
                                class="company-ares-data"
                                id="contact-ares-comparison"
                                hidden
                            >

                                <h3>Porovnání údajů</h3>

                                <p>
                                    Vlevo jsou údaje aktuálně uložené
                                    v databázi. Vpravo jsou údaje načtené
                                    z ARES.
                                </p>

                                <p>
                                    U rozdílných údajů můžete zaškrtnout,
                                    které hodnoty chcete převzít z ARES.
                                </p>

                                <table class="data-table contact-ares-table">

                                    <thead>
                                        <tr>
                                            <th scope="col">Údaj</th>
                                            <th scope="col">Aktuálně v databázi</th>
                                            <th scope="col">ARES</th>
                                            <th scope="col">Převzít</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <tr data-ares-row="officialName">
                                            <th scope="row">Jméno / název</th>

                                            <td data-db-field="officialName">
                                                <?= e($data['official_name'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="officialName"></td>

                                            <td data-ares-select="officialName">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[officialName]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="dic">
                                            <th scope="row">DIČ</th>

                                            <td data-db-field="dic">
                                                <?= e($data['dic'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="dic"></td>

                                            <td data-ares-select="dic">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[dic]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="street">
                                            <th scope="row">Ulice</th>

                                            <td data-db-field="street">
                                                <?= e($data['street'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="street"></td>

                                            <td data-ares-select="street">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[street]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="houseNumber">
                                            <th scope="row">Číslo domu</th>

                                            <td data-db-field="houseNumber">
                                                <?= e($data['house_number'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="houseNumber"></td>

                                            <td data-ares-select="houseNumber">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[houseNumber]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="orientationNumber">
                                            <th scope="row">Číslo orientační</th>

                                            <td data-db-field="orientationNumber">
                                                <?= e($data['orientation_number'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="orientationNumber"></td>

                                            <td data-ares-select="orientationNumber">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[orientationNumber]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="cityPart">
                                            <th scope="row">Část obce</th>

                                            <td data-db-field="cityPart">
                                                <?= e($data['city_part'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="cityPart"></td>

                                            <td data-ares-select="cityPart">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[cityPart]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="city">
                                            <th scope="row">Obec</th>

                                            <td data-db-field="city">
                                                <?= e($data['city'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="city"></td>

                                            <td data-ares-select="city">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[city]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="postalCode">
                                            <th scope="row">PSČ</th>

                                            <td data-db-field="postalCode">
                                                <?= e($data['postal_code'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="postalCode"></td>

                                            <td data-ares-select="postalCode">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[postalCode]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="countryCode">
                                            <th scope="row">Stát</th>

                                            <td data-db-field="countryCode">
                                                <?= e($data['country_code'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="countryCode"></td>

                                            <td data-ares-select="countryCode">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[countryCode]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="deliveryAddress1">
                                            <th scope="row">Doručovací adresa 1</th>

                                            <td data-db-field="deliveryAddress1">
                                                <?= e($data['delivery_address_1'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="deliveryAddress1"></td>

                                            <td data-ares-select="deliveryAddress1">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[deliveryAddress1]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="deliveryAddress2">
                                            <th scope="row">Doručovací adresa 2</th>

                                            <td data-db-field="deliveryAddress2">
                                                <?= e($data['delivery_address_2'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="deliveryAddress2"></td>

                                            <td data-ares-select="deliveryAddress2">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[deliveryAddress2]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                        <tr data-ares-row="deliveryAddress3">
                                            <th scope="row">Doručovací adresa 3</th>

                                            <td data-db-field="deliveryAddress3">
                                                <?= e($data['delivery_address_3'] ?? '') ?>
                                            </td>

                                            <td data-ares-field="deliveryAddress3"></td>

                                            <td data-ares-select="deliveryAddress3">
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="ares_update[deliveryAddress3]"
                                                        value="1"
                                                        hidden
                                                    >
                                                    <span>Převzít</span>
                                                </label>
                                            </td>
                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        <?php endif; ?>


                        <!-- =================================================
                             BĚŽNÁ DATA KONTAKTU
                        ================================================== -->

                        <div class="form-group">

                            <label for="official_name">Název zákazníka</label>

                            <input
                                type="text"
                                name="official_name"
                                id="official_name"
                                value="<?= e($data['official_name'] ?? '') ?>"
                                maxlength="255"
                            >

                        </div>

                        <div class="form-group">

                            <label for="dic">DIČ</label>

                            <input
                                type="text"
                                name="dic"
                                id="dic"
                                value="<?= e($data['dic'] ?? '') ?>"
                                maxlength="32"
                            >

                        </div>

                        <div class="form-group">

                            <label for="street">Ulice</label>

                            <input
                                type="text"
                                name="street"
                                id="street"
                                value="<?= e($data['street'] ?? '') ?>"
                                maxlength="255"
                            >

                        </div>

                        <div class="form-row">

                            <div class="form-group">

                                <label for="house_number">Číslo domu</label>

                                <input
                                    type="text"
                                    name="house_number"
                                    id="house_number"
                                    value="<?= e($data['house_number'] ?? '') ?>"
                                    maxlength="20"
                                >

                            </div>

                            <div class="form-group">

                                <label for="orientation_number">
                                    Číslo orientační
                                </label>

                                <input
                                    type="text"
                                    name="orientation_number"
                                    id="orientation_number"
                                    value="<?= e($data['orientation_number'] ?? '') ?>"
                                    maxlength="20"
                                >

                            </div>

                        </div>

                        <div class="form-group">

                            <label for="city_part">Část obce</label>

                            <input
                                type="text"
                                name="city_part"
                                id="city_part"
                                value="<?= e($data['city_part'] ?? '') ?>"
                                maxlength="255"
                            >

                        </div>

                        <div class="form-group">

                            <label for="city">Obec</label>

                            <input
                                type="text"
                                name="city"
                                id="city"
                                value="<?= e($data['city'] ?? '') ?>"
                                maxlength="255"
                            >

                        </div>

                        <div class="form-group">

                            <label for="postal_code">PSČ</label>

                            <input
                                type="text"
                                name="postal_code"
                                id="postal_code"
                                value="<?= e($data['postal_code'] ?? '') ?>"
                                maxlength="10"
                            >

                        </div>

                        <div class="form-group">

                            <label for="country_code">Stát</label>

                            <input
                                type="text"
                                name="country_code"
                                id="country_code"
                                value="<?= e($data['country_code'] ?? 'CZ') ?>"
                                maxlength="2"
                            >

                        </div>

                        <fieldset>

                            <legend>Doručovací adresa</legend>

                            <div class="form-group">

                                <label for="delivery_address_1">
                                    Adresa 1
                                </label>

                                <input
                                    type="text"
                                    name="delivery_address_1"
                                    id="delivery_address_1"
                                    value="<?= e($data['delivery_address_1'] ?? '') ?>"
                                    maxlength="255"
                                >

                            </div>

                            <div class="form-group">

                                <label for="delivery_address_2">
                                    Adresa 2
                                </label>

                                <input
                                    type="text"
                                    name="delivery_address_2"
                                    id="delivery_address_2"
                                    value="<?= e($data['delivery_address_2'] ?? '') ?>"
                                    maxlength="255"
                                >

                            </div>

                            <div class="form-group">

                                <label for="delivery_address_3">
                                    Adresa 3
                                </label>

                                <input
                                    type="text"
                                    name="delivery_address_3"
                                    id="delivery_address_3"
                                    value="<?= e($data['delivery_address_3'] ?? '') ?>"
                                    maxlength="255"
                                >

                            </div>

                        </fieldset>

                        <fieldset>

                            <legend>Kontakt</legend>

                            <div class="form-group">

                                <label for="email">E-mail</label>

                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="<?= e($data['email'] ?? '') ?>"
                                    maxlength="255"
                                >

                            </div>

                            <div class="form-group">

                                <label for="phone">Telefon</label>

                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    value="<?= e($data['phone'] ?? '') ?>"
                                    maxlength="50"
                                >

                            </div>

                        </fieldset>

                        <fieldset>

                            <legend>Bankovní spojení</legend>

                            <div class="form-row">

                                <div class="form-group">

                                    <label for="bank_account">
                                        Číslo účtu
                                    </label>

                                    <input
                                        type="text"
                                        name="bank_account"
                                        id="bank_account"
                                        value="<?= e($data['bank_account'] ?? '') ?>"
                                        maxlength="50"
                                    >

                                </div>

                                <div class="form-group">

                                    <label for="bank_code">
                                        Kód banky
                                    </label>

                                    <input
                                        type="text"
                                        name="bank_code"
                                        id="bank_code"
                                        value="<?= e($data['bank_code'] ?? '') ?>"
                                        maxlength="10"
                                    >

                                </div>

                            </div>

                        </fieldset>

                        <div class="form-group">

                            <label for="notes">Interní poznámka</label>

                            <textarea
                                name="notes"
                                id="notes"
                                rows="5"
                            ><?= e($data['notes'] ?? '') ?></textarea>

                        </div>

                    </fieldset>

                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <span class="btn-icon"></span>
                            <?= $isEdit ? 'Uložit změny' : 'Uložit zákazníka' ?>
                        </button>

                        <a
                            href="<?= Url::to('/{tenant}/contacts/index/#main') ?>"
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

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>