<?php

declare(strict_types=1);

use App\Core\Url;

/**
 * Část formuláře pro údaje firmy.
 *
 * Používá se ve více formulářích.
 * IČO lze ověřit a údaje firmy načíst z ARES.
 *
 * @var array<string, mixed> $data
 */

?>

<p>
    Pokud zadáte IČO a kliknete na
    <strong>Načíst data z ARES</strong>,
    systém ověří jeho platnost a pokusí se načíst údaje o firmě z ARES.
</p>

<div class="form-group">
    <label>IČO</label>
    <input
        type="text"
        name="ico"
        value="<?= e($data['ico'] ?? '') ?>"
    >
    <div data-ares-message hidden></div>
    <button
        type="button"
        data-ares-load
        data-ares-url="<?= Url::to('/{tenant}/ajax/ares/ico') ?>"
    >
        Načíst data z ARES
    </button>

    
    <details>
        <summary>?</summary>
        <p>
            Osmimístné identifikační číslo organizace.
            Pokud má méně než osm číslic, doplňte zleva nuly.
            U českých subjektů se ověřuje jeho platnost.
        </p>
    </details>
</div>

<div class="form-group">
    <label>Oficiální název: <span class="req">*</span></label>
    <input
        type="text"
        name="official_name"
        value="<?= e($data['official_name'] ?? '') ?>"
        required
    >

    <details>
        <summary>?</summary>
        <p>
            Oficiální název subjektu podle ARES.
            Údaj můžete po načtení z ARES upravit.
        </p>
    </details>
</div>

<div class="form-group">
    <label>DIČ</label>
    <input
        type="text"
        name="dic"
        value="<?= e($data['dic'] ?? '') ?>"
    >

    <details>
        <summary>?</summary>
        <p>
            Daňové identifikační číslo, pokud ho subjekt má.
        </p>
    </details>
</div>

<fieldset>
    <legend>Adresa</legend>
<div class="form-group">
    <label>Ulice</label>
    <input
        type="text"
        name="street"
        value="<?= e($data['street'] ?? '') ?>"
    >
</div>

<div class="form-group">
    <label>Číslo domu</label>
    <input
        type="text"
        name="house_number"
        value="<?= e($data['house_number'] ?? '') ?>"
    >
</div>

<div class="form-group">
    <label>Číslo orientační</label>
    <input
        type="text"
        name="orientation_number"
        value="<?= e($data['orientation_number'] ?? '') ?>"
    >
</div>

<div class="form-group">
    <label>Část obce</label>
    <input
        type="text"
        name="city_part"
        value="<?= e($data['city_part'] ?? '') ?>"
    >
</div>

<div class="form-group">
    <label>Město: <span class="req">*</span></label>
    <input
        type="text"
        name="city"
        value="<?= e($data['city'] ?? '') ?>"
        required
    >
</div>

<div class="form-group">
    <label>PSČ: <span class="req">*</span></label>
    <input
        type="text"
        name="postal_code"
        value="<?= e($data['postal_code'] ?? '') ?>"
        required
    >
</div>

<div class="form-group">
    <label>Stát: <span class="req">*</span></label>
    <input
        type="text"
        name="country_code"
        value="<?= e($data['country_code'] ?? 'CZ') ?>"
        required
    >
</div>
</fieldset>
<fieldset>
    <legend>Doručovací adresa</legend>

    <div class="form-group">
        <label>Řádek 1</label>
        <input
            type="text"
            name="delivery_address_1"
            value="<?= e($data['delivery_address_1'] ?? '') ?>"
        >
    </div>

    <div class="form-group">
        <label>Řádek 2</label>
        <input
            type="text"
            name="delivery_address_2"
            value="<?= e($data['delivery_address_2'] ?? '') ?>"
        >
    </div>

    <div class="form-group">
        <label>Řádek 3</label>
        <input
            type="text"
            name="delivery_address_3"
            value="<?= e($data['delivery_address_3'] ?? '') ?>"
        >
    </div>
</fieldset>
<fieldset>
    <legend>Kontakt</legend>

    <div class="form-group">
        <label for="email">
            E-mail pro zasílání faktur
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= e($data['email'] ?? '') ?>"
        >
    </div>

    <div class="form-group">
        <label for="phone">
            Telefon
        </label>

        <input
            type="text"
            id="phone"
            name="phone"
            value="<?= e($data['phone'] ?? '') ?>"
        >
    </div>

</fieldset>
<fieldset>
        <legend>Bankovní spojení</legend>

        <div class="form-group">
            <label for="bank_account">
                Číslo účtu
            </label>

            <input
                type="text"
                id="bank_account"
                name="bank_account"
                value="<?= e($data['bank_account'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="bank_code">
                Kód banky
            </label>

            <input
                type="text"
                id="bank_code"
                name="bank_code"
                value="<?= e($data['bank_code'] ?? '') ?>"
                maxlength="10"
            >
        </div>
   

</fieldset>