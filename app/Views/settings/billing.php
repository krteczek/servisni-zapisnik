<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;
use App\Core\Csrf;
use App\Services\Settings\BillingMode;
$data = $view->data;
// var_dump($data['myReadyToDoneOrders']); 

?>
<div class="settings-grid">
    <div class="card">

        <div class="card-header">
            Fakturace
        </div>

        <div class="card-body">

            <form method="post"
                action="<?= Url::to('/{tenant}/system/settings/billing') ?>">

                <?= Csrf::getField() ?>

                <div class="form-group">
                    <label for="billing_mode">
                        Režim fakturace
                    </label>
                    <small>Jestli budete vytvářet faktury v Bó systému (typicky řemeslníci a malé firmy, které neřeší DPH) nebo si jen vygenerujete podklady pro účetní oddělení (Větší firmy, řeší se DPH, účetní). </small>
    <select name="billing_mode" id="billing_mode">
    <?php 
    if ($data['billing']['billing_mode'] === BillingMode::EXTERNAL_ACCOUNTANT) {
        $current =  BillingMode::EXTERNAL_ACCOUNTANT;
    } else {
        $current = BillingMode::INTERNAL;
    }
    ?>
    <?php foreach (BillingMode::labels() as $value => $label): ?>
        <option value="<?= e($value) ?>" <?= ($current === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
    <?php endforeach; ?>
    </select>
                </div>
                <div class="form-group">
                    <label for="invoice_due_days">
                        Splatnost faktur
                    </label>
                    <small>Kolik dnů dáváte klientům k zaplacení Vámi vydaných faktur od jejich vystavení.</small>
                    <input type="number"
                        step="1"
                        min="1"
                        name="invoice_due_days"
                        id="invoice_due_days"
                        class="form-control"
                        value="<?= (int) ($data['billing']['invoice_due_days'] ?? 14) ?>"
                        placeholder="14">            
                </div>

                <button type="submit"
                        class="btn btn-primary">
                    Uložit nastavení fakturace
                </button>

            </form>

        </div>

    </div>

</div><!-- .settings-grid -->