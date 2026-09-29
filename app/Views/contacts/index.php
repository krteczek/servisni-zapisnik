<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$contacts = $view->contacts;
?>

<div class="entity-grid">
<?php if ($contacts === []): ?>
    <div class="users-empty">
        <strong>Žádní zákazníci</strong>
        <p>Zatím zde není žádný zákazník.</p>
    </div>

<?php else: ?>
    <?php foreach ($contacts as $contact): ?>
        <div class="card">

            <!-- HLAVIČKA KARTY -->
            <div class="card-header">
                <span class="card-title">
                    <a
                        href="<?= Url::to(
                            '/{tenant}/contacts/' . $contact['id'] . '/detail/#main'
                        ) ?>"
                        title="Jít na detail zákazníka"
                    >
                        <?= e($contact['official_name']) ?>
                    </a>
                </span>
            </div>

            <!-- TĚLO KARTY -->
            <div class="card-body">
                <div class="meta-list">

                    <div class="meta-item">
                        <span class="meta-label">IČO: </span>
                        <span class="meta-value">
                            <?= e($contact['ico'] !== '' ? $contact['ico'] : '-') ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">DIČ: </span>
                        <span class="meta-value">
                            <?= e($contact['dic'] !== '' ? $contact['dic'] : '-') ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">Ulice: </span>
                        <span class="meta-value">
                            <?= e($contact['street'] !== '' ? $contact['street'] : '-') ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">Město: </span>
                        <span class="meta-value">
                            <?= e($contact['city'] !== '' ? $contact['city'] : '-') ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">PSČ: </span>
                        <span class="meta-value">
                            <?= e(
                                $contact['postal_code'] !== ''
                                    ? $contact['postal_code']
                                    : '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">Stát: </span>
                        <span class="meta-value">
                            <?= e(
                                $contact['country_code'] !== ''
                                    ? $contact['country_code']
                                    : '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">E-mail: </span>
                        <span class="meta-value">
                            <?= e(
                                $contact['email'] !== ''
                                    ? $contact['email']
                                    : '-'
                            ) ?>
                        </span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">Telefon: </span>
                        <span class="meta-value">
                            <?= e(
                                $contact['phone'] !== ''
                                    ? $contact['phone']
                                    : '-'
                            ) ?>
                        </span>
                    </div>

                </div>
            </div>

            <!-- PATIČKA KARTY -->
            <div class="card-footer">
                <div class="actions">
                    <a
                        href="<?= Url::to(
                            '/{tenant}/contacts/' . $contact['id'] . '/edit/#main'
                        ) ?>"
                        class="btn btn-secondary"
                        title="Upravit informace o zákazníkovi"
                    >
                        ✏️ Upravit
                    </a>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php';