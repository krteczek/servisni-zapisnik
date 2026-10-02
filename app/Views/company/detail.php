<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$data = $view->data;

$company = $data['company'] ?? [];
$details = $data['details'] ?? [];
?>

<div class="create-container">

    <div class="card">

        <div class="card-body">

            <div class="form-container">

                <fieldset>
                    <legend>Identifikace firmy</legend>

                    <dl class="company-details">

                        <dt>IČO</dt>
                        <dd><?= e($company['ico'] ?? '') ?></dd>

                        <dt>Jméno / název</dt>
                        <dd><?= e($details['official_name'] ?? '') ?></dd>

                        <?php if (($details['trade_name'] ?? '') !== ''): ?>
                            <dt>Obchodní název</dt>
                            <dd><?= e($details['trade_name']) ?></dd>
                        <?php endif; ?>

                        <?php if (($details['dic'] ?? '') !== ''): ?>
                            <dt>DIČ</dt>
                            <dd><?= e($details['dic']) ?></dd>
                        <?php endif; ?>

                    </dl>

                </fieldset>


                <fieldset>
                    <legend>Sídlo</legend>

                    <dl class="company-details">

                        <?php if (($details['street'] ?? '') !== ''): ?>
                            <dt>Ulice</dt>
                            <dd>
                                <?= e($details['street']) ?>
                                <?php if (($details['house_number'] ?? '') !== ''): ?>
                                    <?= ' ' . e($details['house_number']) ?>
                                <?php endif; ?>
                                <?php if (($details['orientation_number'] ?? '') !== ''): ?>
                                    /<?= e($details['orientation_number']) ?>
                                <?php endif; ?>
                            </dd>
                        <?php endif; ?>

                        <?php if (($details['city_part'] ?? '') !== ''): ?>
                            <dt>Část obce</dt>
                            <dd><?= e($details['city_part']) ?></dd>
                        <?php endif; ?>

                        <dt>Obec</dt>
                        <dd><?= e($details['city'] ?? '') ?></dd>

                        <dt>PSČ</dt>
                        <dd><?= e($details['postal_code'] ?? '') ?></dd>

                        <dt>Stát</dt>
                        <dd><?= e($details['country_code'] ?? '') ?></dd>

                    </dl>

                </fieldset>


                <?php
                $deliveryAddress = array_filter([
                    $details['delivery_address_1'] ?? null,
                    $details['delivery_address_2'] ?? null,
                    $details['delivery_address_3'] ?? null,
                ], static fn (mixed $value): bool => is_string($value) && trim($value) !== '');
                ?>

                <?php if ($deliveryAddress !== []): ?>

                    <fieldset>
                        <legend>Doručovací adresa</legend>

                        <p>
                            <?php foreach ($deliveryAddress as $line): ?>
                                <?= e($line) ?><br>
                            <?php endforeach; ?>
                        </p>

                    </fieldset>

                <?php endif; ?>


                <fieldset>
                    <legend>Údaje z ARES</legend>

                    <dl class="company-details">

                        <?php if (($details['legal_form_code'] ?? '') !== ''): ?>
                            <dt>Právní forma</dt>
                            <dd><?= e($details['legal_form_code']) ?></dd>
                        <?php endif; ?>

                        <?php if (($details['founded_at'] ?? '') !== ''): ?>
                            <dt>Datum vzniku</dt>
                            <dd><?= e($details['founded_at']) ?></dd>
                        <?php endif; ?>

                        <?php if (($details['ares_updated_at'] ?? '') !== ''): ?>
                            <dt>Aktualizace v ARES</dt>
                            <dd><?= e($details['ares_updated_at']) ?></dd>
                        <?php endif; ?>

                    </dl>

                    <p class="text-muted">
                        Identifikační a adresní údaje jsou převzaty z ARES
                        a v aplikaci se neupravují ručně.
                    </p>

                </fieldset>


                <div class="form-actions">

                    <a href="<?= Url::to('/{tenant}/system/company/edit') ?>/#main"
                       class="btn btn-primary">
                        <span class="btn-icon">✎</span>
                        Změnit údaje
                    </a>

                    <a href="<?= Url::to('/{tenant}/system/company/detail') ?>/#main"
                       class="btn btn-secondary">
                        <span class="btn-icon">←</span>
                        Zpět
                    </a>

                </div>

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
                    <li>IČO je převzato z firemního účtu.</li>
                    <li>Údaje firmy byly načteny z ARES.</li>
                    <li>Obchodní název je vlastní údaj firmy.</li>
                    <li>Obchodní název lze kdykoliv změnit.</li>
                </ul>

            </details>

        </div>

    </div>

</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>