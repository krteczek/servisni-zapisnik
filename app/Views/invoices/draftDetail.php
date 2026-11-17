<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;
use App\Services\Invoice\InvoiceStatus;

require __DIR__ . '/../layout/header.php';

$invoice = $view->invoice;

$data      = $invoice['invoice']   ?? [];
$customer  = $invoice['customer']  ?? [];
$workOrder = $invoice['workOrder'] ?? [];
$tasks     = $invoice['tasks']     ?? [];
$lines     = $invoice['lines']     ?? [];

$status    = (string) ($invoice['status'] ?? '');
$invoiceId = (int) ($invoice['id'] ?? 0);

$isDraft     = InvoiceStatus::isDraft($status);
$isIssued    = InvoiceStatus::isIssued($status);
$isPaid      = InvoiceStatus::isPaid($status);
$isCancelled = InvoiceStatus::isCancelled($status);

$statusLabels = [
    InvoiceStatus::DRAFT     => 'Koncept',
    InvoiceStatus::ISSUED    => 'Vydaná',
    InvoiceStatus::PAID      => 'Zaplacená',
    InvoiceStatus::CANCELLED => 'Stornovaná',
];

$total = 0.0;
foreach ($lines as $line) {
    $total += (float) ($line['total'] ?? 0);
}
?>

<div class="invoice-detail">

    <header class="invoice-detail-header">
        <h1>
            Faktura <?= e((string) ($invoice['invoice_number'] ?? '')) ?>
        </h1>

        <span class="invoice-status invoice-status-<?= e($status) ?>">
            <?= e($statusLabels[$status] ?? $status) ?>
        </span>

        <div class="invoice-detail-meta">
            <div>
                <strong>Vystaveno:</strong>
                <?= e((string) ($invoice['issued_at'] ?? '')) ?>
            </div>
            <div>
                <strong>Splatnost:</strong>
                <?= e((string) ($invoice['due_date'] ?? '')) ?>
            </div>
            <div>
                <strong>Zákazník:</strong>
                <?= e((string) ($invoice['customer_name'] ?? '')) ?>
            </div>
        </div>
    </header>

    <!-- ============================================================
         SEKCE 1: FAKTURA (to, co jde na papír)
         ============================================================ -->

    <section class="invoice-detail-parties">
        <div>
            <h2>Odběratel</h2>

            <strong>
                <?= e((string) ($customer['official_name'] ?? '')) ?>
            </strong>

            <?php if (($customer['ico'] ?? '') !== ''): ?>
                <div>IČO: <?= e((string) $customer['ico']) ?></div>
            <?php endif; ?>

            <?php if (($customer['dic'] ?? '') !== ''): ?>
                <div>DIČ: <?= e((string) $customer['dic']) ?></div>
            <?php endif; ?>

            <?php
            $street = trim(
                (string) ($customer['street'] ?? '') . ' ' .
                (string) ($customer['house_number'] ?? '')
            );
            ?>

            <?php if ($street !== ''): ?>
                <div><?= e($street) ?></div>
            <?php endif; ?>

            <?php if (($customer['city'] ?? '') !== ''): ?>
                <div>
                    <?= e((string) ($customer['postal_code'] ?? '')) ?>
                    <?= e((string) $customer['city']) ?>
                </div>
            <?php endif; ?>

            <?php if (($customer['email'] ?? '') !== ''): ?>
                <div>E-mail: <?= e((string) $customer['email']) ?></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="invoice-detail-lines">
        <h2>Fakturační položky</h2>

        <?php if ($lines === []): ?>
            <p class="text-muted">
                Faktura neobsahuje žádné položky.
            </p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Popis</th>
                        <th>Množství</th>
                        <th>Jednotka</th>
                        <th>Cena za</th>
                        <th>Cena</th>
                        <th>Celkem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                        <?php
                        $unit = (string) ($line['unit'] ?? '');
                        $qty  = (float) ($line['quantity'] ?? 0);

                        // Pro minuty zobrazíme hezky "X h Y min".
                        if ($unit === 'min') {
                            $mins = (int) $qty;
                            $h = intdiv($mins, 60);
                            $m = $mins % 60;
                            $qtyLabel = trim(
                                ($h > 0 ? $h . ' h' : '') .
                                ($m > 0 ? ' ' . $m . ' min' : '')
                            );
                            if ($qtyLabel === '') {
                                $qtyLabel = '0 min';
                            }
                        } else {
                            $qtyLabel = (string) $qty;
                        }
                        ?>
                        <tr>
                            <td><?= e((string) ($line['description'] ?? '')) ?></td>
                            <td><?= e($qtyLabel) ?></td>
                            <td><?= e($unit) ?></td>
                            <td><?= e((string) ($line['price_unit'] ?? '')) ?></td>
                            <td>
                                <?= e(number_format(
                                    (float) ($line['unit_price'] ?? 0),
                                    2,
                                    ',',
                                    ' '
                                )) ?>
                            </td>
                            <td>
                                <?= e(number_format(
                                    (float) ($line['total'] ?? 0),
                                    2,
                                    ',',
                                    ' '
                                )) ?>
                                <?= e((string) ($line['currency'] ?? 'CZK')) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5">Celkem</th>
                        <th>
                            <?= e(number_format($total, 2, ',', ' ')) ?>
                            CZK
                        </th>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </section>

    <?php if (($data['note'] ?? '') !== ''): ?>
        <section class="invoice-detail-note">
            <h2>Poznámka</h2>
            <p><?= nl2br(e((string) $data['note'])) ?></p>
        </section>
    <?php endif; ?>

    <!-- ============================================================
         SEKCE 2: PODKLADY (interní, jen pokud existují)
         ============================================================ -->

    <?php if ($tasks !== [] || $workOrder !== []): ?>
        <details class="invoice-sources">
            <summary>Podklady (interní)</summary>

            <?php if ($workOrder !== []): ?>
                <section class="invoice-detail-workorder">
                    <h3>Zakázka</h3>

                    <div>
                        <strong>
                            <?= e((string) ($workOrder['title'] ?? '')) ?>
                        </strong>
                    </div>

                    <?php if (($workOrder['description'] ?? '') !== ''): ?>
                        <p><?= e((string) $workOrder['description']) ?></p>
                    <?php endif; ?>

                    <?php if (($workOrder['price_per_hour'] ?? 0) > 0): ?>
                        <div class="text-muted">
                            Sazba: <?= e((string) $workOrder['price_per_hour']) ?> Kč/h
                            <?php if (($workOrder['price_per_km'] ?? 0) > 0): ?>
                                , <?= e((string) $workOrder['price_per_km']) ?> Kč/km
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($tasks !== []): ?>
                <section class="invoice-detail-tasks">
                    <h3>Úkoly</h3>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Úkol</th>
                                <th>Čas</th>
                                <th>Km</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <?php
                                $minutes = (int) ($task['minutes'] ?? 0);
                                $hours   = intdiv($minutes, 60);
                                $rest    = $minutes % 60;
                                ?>
                                <tr>
                                    <td>
                                        <?= e((string) ($task['title'] ?? '')) ?>
                                    </td>
                                    <td>
                                        <?= $hours ?> h
                                        <?php if ($rest > 0): ?>
                                            <?= $rest ?> min
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= e((string) ($task['kilometers'] ?? 0)) ?> km
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>
        </details>
    <?php endif; ?>

    <!-- ============================================================
         AKCE
         ============================================================ -->

    <div class="form-actions">
        
            <form
                method="post"
                action="<?= Url::to(
                    '/{tenant}/billing/invoice/draft/' . $invoiceId . '/issue'
                ) ?>"
                style="display:inline"
            >
                <?= Csrf::getField() ?>
                <button type="submit" class="btn btn-primary">
                    Vydat fakturu
                </button>
            </form>
    
            <form
                method="post"
                action="<?= Url::to(
                    '/{tenant}/billing/invoice/draft/' . $invoiceId . '/discard'
                ) ?>"
                style="display:inline"
                onsubmit="return confirm('Opravdu zahodit návrh faktury?');"
            >
                <?= Csrf::getField() ?>
                <button type="submit" class="btn btn-danger">
                    Zahodit návrh faktury
                </button>
            </form>
            <a
                href="<?= Url::to(
                    '/{tenant}/billing/invoice/draft/' . $invoiceId . '/edit/#main'
                ) ?>"
                class="btn btn-secondary"
                title="Upravit návrh faktury"
            >
                Upravit návrh faktury
            </a>


        <a
            href="<?= Url::to('/{tenant}/billing/invoice/#main') ?>"
            class="btn btn-secondary"
        >
            Zpět
        </a>
    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php';