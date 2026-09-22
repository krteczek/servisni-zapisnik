<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;
use App\Services\Settings\BillingMode;

$data = $view->data;
$billing = $data['billing'];

$confirmed = $billing['_confirmed'] ?? [];

$numberStartConfirmed =
    ($confirmed['invoice_number_start'] ?? false) === true;

$numberFormatConfirmed =
    ($confirmed['invoice_number_format'] ?? false) === true;

require __DIR__ . '/../layout/header.php';
?>

<div class="settings-grid">

    <!-- Režim fakturace -->
    <div class="card">

        <div class="card-header">
            Režim fakturace
        </div>

        <div class="card-body">

            <form method="post"
                  action="<?= Url::to('/{tenant}/system/settings/billing/mode') ?>">

                <?= Csrf::getField() ?>

                <div class="form-group">

                    <label for="billing_mode">
                        Režim fakturace
                    </label>

                    <small>
                        Určuje, zda budete vytvářet faktury přímo v Bó,
                        nebo pouze generovat podklady pro externí účetnictví.
                    </small>

                    <?php
                    $currentMode = $billing['billing_mode'] ?? BillingMode::INTERNAL;
                    ?>

                    <select
                        name="billing_mode"
                        id="billing_mode"
                        class="form-control"
                    >

                        <?php foreach (BillingMode::labels() as $value => $label): ?>

                            <option
                                value="<?= e($value) ?>"
                                <?= $currentMode === $value ? 'selected' : '' ?>
                            >
                                <?= e($label) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button type="submit" class="btn btn-primary">
                    Uložit režim fakturace
                </button>

            </form>

        </div>

    </div>


    <!-- Splatnost faktur -->
    <div class="card">

        <div class="card-header">
            Splatnost faktur
        </div>

        <div class="card-body">

            <form method="post"
                  action="<?= Url::to('/{tenant}/system/settings/billing/due-days') ?>">

                <?= Csrf::getField() ?>

                <div class="form-group">

                    <label for="invoice_due_days">
                        Počet dnů splatnosti
                    </label>

                    <small>
                        Počet dnů od vystavení faktury, během kterých má
                        zákazník fakturu uhradit.
                    </small>

                    <input
                        type="number"
                        step="1"
                        min="1"
                        max="365"
                        name="invoice_due_days"
                        id="invoice_due_days"
                        class="form-control"
                        value="<?= (int) ($billing['invoice_due_days'] ?? 14) ?>"
                    >

                </div>

                <button type="submit" class="btn btn-primary">
                    Uložit splatnost
                </button>

            </form>

        </div>

    </div>


    <!-- Počáteční číslo -->
    <div class="card">

        <div class="card-header">
            Číselná řada faktur
        </div>

        <div class="card-body">

            <form method="post"
                  action="<?= Url::to('/{tenant}/system/settings/billing/number-start') ?>">

                <?= Csrf::getField() ?>

                <div class="form-group">

                    <label for="invoice_number_start">
                        Počáteční číslo
                    </label>

                    <small>
                        Číslo, od kterého bude začínat číselná řada faktur.
                        V průběhu roku nelze nastavit číslo nižší než nejvyšší
                        dosud použité číslo.
                    </small>

                    <input
                        type="number"
                        step="1"
                        min="1"
                        name="invoice_number_start"
                        id="invoice_number_start"
                        class="form-control"
                        value="<?= (int) ($billing['invoice_number_start'] ?? 1) ?>"
                    >

                </div>

                <button type="submit" class="btn btn-primary">
                    Uložit počáteční číslo
                </button>

            </form>


            <?php if ($numberStartConfirmed): ?>

                <p>
                    <strong>Nastavení je potvrzené.</strong>
                </p>

            <?php else: ?>

                <p>
                    Nastavení zatím není potvrzené.
                </p>

                <form method="post"
                      action="<?= Url::to('/{tenant}/system/settings/billing/number-start/confirm') ?>">

                    <?= Csrf::getField() ?>

                    <button type="submit" class="btn btn-secondary">
                        Potvrdit počáteční číslo
                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>


    <!-- Formát čísla -->
    <div class="card">

        <div class="card-header">
            Formát čísla faktury
        </div>

        <div class="card-body">

            <form method="post"
                  action="<?= Url::to('/{tenant}/system/settings/billing/number-format') ?>">

                <?= Csrf::getField() ?>

                <div class="form-group">

                    <label for="invoice_number_format">
                        Formát čísla
                    </label>

                    <small>
                        Například <code>{R5}</code> znamená rok a
                        pětimístné pořadové číslo.
                    </small>

                    <input
                        type="text"
                        name="invoice_number_format"
                        id="invoice_number_format"
                        class="form-control"
                        value="<?= e((string) ($billing['invoice_number_format'] ?? '{R5}')) ?>"
                    >

                </div>

                <button type="submit" class="btn btn-primary">
                    Uložit formát
                </button>

            </form>


            <?php if ($numberFormatConfirmed): ?>

                <p>
                    <strong>Nastavení je potvrzené.</strong>
                </p>

            <?php else: ?>

                <p>
                    Nastavení zatím není potvrzené.
                </p>

                <form method="post"
                      action="<?= Url::to('/{tenant}/system/settings/billing/number-format/confirm') ?>">

                    <?= Csrf::getField() ?>

                    <button type="submit" class="btn btn-secondary">
                        Potvrdit formát
                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>

</div><!-- .settings-grid -->

<?php require __DIR__ . '/../layout/footer.php'; ?>