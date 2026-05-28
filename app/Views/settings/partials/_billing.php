<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;
use App\Core\Csrf;
use App\Services\Settings\BillingMode;
$data = $view->data;
// var_dump($data['myReadyToDoneOrders']);

?><div class="card">

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

            <button type="submit"
                    class="btn btn-primary">
                Uložit nastavení fakturace
            </button>

        </form>

    </div>

</div>