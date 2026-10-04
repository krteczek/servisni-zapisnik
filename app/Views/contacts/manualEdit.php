<?php

declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$data   = $view->data;
$errors = $view->errors;

$id = (int) ($data['id'] ?? 0);
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

                    <legend>Ruční úprava zákazníka</legend>

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

                        <label for="ico">IČO</label>

                        <input
                            type="text"
                            name="ico"
                            id="ico"
                            value="<?= e($data['ico'] ?? '') ?>"
                            readonly
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
                        Uložit změny
                    </button>

                    <a
                        href="<?= Url::to('/{tenant}/contacts/' . $id . '/detail/#main') ?>"
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