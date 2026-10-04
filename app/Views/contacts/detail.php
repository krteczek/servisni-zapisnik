<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$data = $view->data;
?>

<div class="create-container">

    <div class="card">

        <div class="card-body">

            <div class="form-container">

                <fieldset>
                    <legend>Údaje zákazníka</legend>

                    <dl class="company-details">

                        <dt>IČO</dt>
                        <dd><?= e($data['ico'] ?? '') ?></dd>

                        <dt>Oficiální název</dt>
                        <dd><?= e($data['official_name'] ?? '') ?></dd>

                        <dt>DIČ</dt>
                        <dd><?= e($data['dic'] ?? '') ?></dd>

                    </dl>
                </fieldset>


                <fieldset>
                    <legend>Adresa</legend>

                    <dl class="company-details">

                        <dt>Ulice</dt>
                        <dd>
                            <?= e($data['street'] ?? '') ?>
                            <?php if (($data['house_number'] ?? '') !== ''): ?>
                                <?= ' ' . e($data['house_number']) ?>
                            <?php endif; ?>
                            <?php if (($data['orientation_number'] ?? '') !== ''): ?>
                                <?= '/' . e($data['orientation_number']) ?>
                            <?php endif; ?>
                        </dd>

                        <dt>Část obce</dt>
                        <dd><?= e($data['city_part'] ?? '') ?></dd>

                        <dt>Obec</dt>
                        <dd><?= e($data['city'] ?? '') ?></dd>

                        <dt>PSČ</dt>
                        <dd><?= e($data['postal_code'] ?? '') ?></dd>

                        <dt>Stát</dt>
                        <dd><?= e($data['country_code'] ?? '') ?></dd>

                    </dl>
                </fieldset>


                <?php
                $delivery1 = trim((string) ($data['delivery_address_1'] ?? ''));
                $delivery2 = trim((string) ($data['delivery_address_2'] ?? ''));
                $delivery3 = trim((string) ($data['delivery_address_3'] ?? ''));
                ?>

                <?php if ($delivery1 !== '' || $delivery2 !== '' || $delivery3 !== ''): ?>

                    <fieldset>
                        <legend>Doručovací adresa</legend>

                        <p>
                            <?php if ($delivery1 !== ''): ?>
                                <?= e($delivery1) ?><br>
                            <?php endif; ?>

                            <?php if ($delivery2 !== ''): ?>
                                <?= e($delivery2) ?><br>
                            <?php endif; ?>

                            <?php if ($delivery3 !== ''): ?>
                                <?= e($delivery3) ?>
                            <?php endif; ?>
                        </p>

                    </fieldset>

                <?php endif; ?>


                <fieldset>
                    <legend>Kontakt</legend>

                    <dl class="company-details">

                        <dt>E-mail pro zasílání faktur</dt>
                        <dd>
                            <?php if (($data['email'] ?? '') !== ''): ?>
                                <a href="mailto:<?= e($data['email']) ?>">
                                    <?= e($data['email']) ?>
                                </a>
                            <?php endif; ?>
                        </dd>

                        <dt>Telefon</dt>
                        <dd><?= e($data['phone'] ?? '') ?></dd>

                    </dl>
                </fieldset>


                <fieldset>
                    <legend>Bankovní spojení</legend>

                    <dl class="company-details">

                        <dt>Číslo účtu</dt>
                        <dd><?= e($data['bank_account'] ?? '') ?></dd>

                        <dt>Kód banky</dt>
                        <dd><?= e($data['bank_code'] ?? '') ?></dd>

                    </dl>
                </fieldset>


                <?php
                $notes = trim((string) ($data['notes'] ?? ''));
                ?>

                <?php if ($notes !== ''): ?>

                    <fieldset>
                        <legend>Interní poznámky k zákazníkovi</legend>

                        <p>
                            <?= nl2br(e($notes)) ?>
                        </p>

                    </fieldset>

                <?php endif; ?>


                <div class="form-actions">

                    <a
                        href="<?= Url::to('/{tenant}/contacts/' . (int) $data['id'] . '/manualEdit/#main') ?>"
                        class="btn btn-primary" title="Ruční úprava údajů o zákazníkovi"
                    >
                        <span class="btn-icon">✎</span>
                        Ruční úprava
                    </a>
                    <a
                        href="<?= Url::to('/{tenant}/contacts/' . (int) $data['id'] . '/aresEdit/#main') ?>"
                        class="btn btn-primary" title="Aktualizace údajů o zákazníkovi pomocí ARES"
                    >
                        <span class="btn-icon">✎</span>
                        Úprava pomocí ARES
                    </a>

                    <a
                        href="<?= Url::to('/{tenant}/contacts/list/#main') ?>"
                        class="btn btn-secondary"
                    >
                        <span class="btn-icon">←</span>
                        Na výpis zákazníků
                    </a>

                </div>

            </div>

        </div>
    </div>


    <div class="card card-help" id="helpCard">

        <div class="card-body">

            <details>

                <summary>Informace</summary>

                <p>
                    Detail zákazníka obsahuje identifikační, adresní,
                    kontaktní a bankovní údaje zákazníka.
                </p>

                <p>
                    Interní poznámky jsou určeny pro pracovníky
                    a nejsou součástí údajů načítaných z ARES.
                </p>

            </details>

        </div>

    </div>

</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>