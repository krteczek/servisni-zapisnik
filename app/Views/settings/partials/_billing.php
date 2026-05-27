<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;
use App\Core\Csrf;

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

                <select name="billing_mode"
                        id="billing_mode">

                    <option value="internal"
                        <?= ($data['billing']['billing_mode'] ?? '') === 'internal'
                            ? 'selected'
                            : '' ?>>
                        Interní jednoduché faktury
                    </option>

                    <option value="external_accountant"
                        <?= ($data['billing']['billing_mode'] ?? '') === 'external_accountant'
                            ? 'selected'
                            : '' ?>>
                        Exporty pro účetní
                    </option>

                </select>

            </div>

            <button type="submit"
                    class="btn btn-primary">
                Uložit nastavení fakturace
            </button>

        </form>

    </div>

</div>