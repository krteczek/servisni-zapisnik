<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Services\Invoice\InvoiceStatus;
use dfridrich\QRPlatba\QRPlatba;

$invoice  = $view->invoice;
$supplier = $view->supplier;

$data      = $invoice['invoice']   ?? [];
$customer  = $invoice['customer']  ?? [];
$workOrder = $invoice['workOrder'] ?? [];
$tasks     = $invoice['tasks']     ?? [];
$lines     = $invoice['lines']     ?? [];

$status    = (string) ($invoice['status'] ?? '');
$invoiceNumber = (string) ($invoice['invoice_number'] ?? '');
$issuedAt = (string) ($invoice['issued_at'] ?? '');
$dueDate  = (string) ($invoice['due_date'] ?? '');

$isCancelled = InvoiceStatus::isCancelled($status);

/*
 * Formát data: Y-m-d → d.m.Y
 */
$formatDate = static function (string $date): string {
    if ($date === '') {
        return '';
    }

    $dt = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    if ($dt === false) {
        return $date;
    }

    return $dt->format('d.m.Y');
};

$total = 0.0;
foreach ($lines as $line) {
    $total += (float) ($line['total'] ?? 0);
}

/*
 * Dodavatel – adresa
 */
$supplierStreet = trim(
    (string) ($supplier['street'] ?? '') . ' ' .
    (string) ($supplier['house_number'] ?? '') .
    (
        (string) ($supplier['orientation_number'] ?? '') !== ''
            ? '/' . $supplier['orientation_number']
            : ''
    )
);

$supplierCity = trim(
    (string) ($supplier['postal_code'] ?? '') . ' ' .
    (string) ($supplier['city'] ?? '')
);

/*
 * Odběratel – adresa
 */
$customerStreet = trim(
    (string) ($customer['street'] ?? '') . ' ' .
    (string) ($customer['house_number'] ?? '') .
    (
        (string) ($customer['orientation_number'] ?? '') !== ''
            ? '/' . $customer['orientation_number']
            : ''
    )
);

$customerCity = trim(
    (string) ($customer['postal_code'] ?? '') . ' ' .
    (string) ($customer['city'] ?? '')
);

/*
 * Bankovní účet – default z bank_accounts.
 */
$bankAccounts = $supplier['bank_accounts'] ?? [];
$defaultAccount = null;

foreach ($bankAccounts as $account) {
    if ((int) ($account['is_default'] ?? 0) === 1) {
        $defaultAccount = $account;
        break;
    }
}

if ($defaultAccount === null && $bankAccounts !== []) {
    $defaultAccount = $bankAccounts[0];
}

/*
 * Sestaví řetězec čísla účtu: "prefix-number/bank_code"
 */
$accountText = '';

if (is_array($defaultAccount)) {
    $parts = [];

    if ((string) ($defaultAccount['account_prefix'] ?? '') !== '') {
        $parts[] = $defaultAccount['account_prefix'];
    }

    if ((string) ($defaultAccount['account_number'] ?? '') !== '') {
        $parts[] = $defaultAccount['account_number'];
    }

    $accountText = implode('-', $parts);

    if ((string) ($defaultAccount['bank_code'] ?? '') !== '') {
        $accountText .= '/' . $defaultAccount['bank_code'];
    }
}

/*
 * QR Platba (dfridrich/qr-platba)
 */
$qrHtml = '';

if ($defaultAccount !== null && $total > 0) {
    try {
        $qrPlatba = new QRPlatba();

        if ((string) ($defaultAccount['iban'] ?? '') !== '') {
            $qrPlatba->setIBAN((string) $defaultAccount['iban']);
        } elseif ($accountText !== '') {
            $qrPlatba->setAccount($accountText);
        }

        $qrPlatba
            ->setVariableSymbol($invoiceNumber)
            ->setAmount($total)
            ->setMessage('Faktura ' . $invoiceNumber);

        if ($dueDate !== '') {
            $dt = \DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $dueDate
            );

            if ($dt !== false) {
                $qrPlatba->setDueDate($dt);
            }
        }

        $qrHtml = $qrPlatba->getQRCodeImage();
    } catch (\Throwable $e) {
        // QR je volitelný – když selže, PDF se vygeneruje bez něj.
        $qrHtml = '';
    }
}

?><!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Faktura <?= e($invoiceNumber) ?></title>

    <style>
        @page {
            margin: 18mm 16mm 20mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #222;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3 {
            margin: 0 0 6px 0;
        }

        .header {
            width: 100%;
            margin-bottom: 22px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: top;
        }

        .supplier {
            width: 55%;
        }

        .supplier .name {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .supplier .line {
            margin: 2px 0;
        }

        .invoice-title {
            width: 45%;
            text-align: right;
        }

        .invoice-title .label {
            font-size: 20pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }

        .invoice-title .number {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .meta {
            font-size: 10pt;
        }

        .meta .row {
            margin: 3px 0;
        }

        .meta .label {
            color: #666;
            display: inline-block;
            min-width: 110px;
            text-align: left;
        }

        .parties {
            width: 100%;
            margin-bottom: 18px;
        }

        .parties table {
            width: 100%;
            border-collapse: collapse;
        }

        .parties td {
            vertical-align: top;
            padding: 0;
        }

        .parties .label {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .parties .name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .parties .line {
            margin: 2px 0;
        }

        table.lines {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.lines th {
            background: #f0f0f0;
            text-align: left;
            padding: 6px 8px;
            font-size: 9pt;
            border-bottom: 1px solid #ccc;
        }

        table.lines td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        table.lines td.num,
        table.lines th.num {
            text-align: right;
            white-space: nowrap;
        }

        table.lines tfoot td {
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 8px;
        }

        .total {
            font-size: 12pt;
        }

        .payment {
            margin-top: 14px;
            width: 100%;
        }

        .payment table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment td {
            vertical-align: top;
            padding: 0;
        }

        .payment .qr {
            width: 35mm;
            text-align: right;
        }

        .payment .qr img {
            width: 35mm;
            height: 35mm;
        }

        .payment .label {
            color: #666;
            font-size: 8pt;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .payment .line {
            margin: 2px 0;
        }

        .note {
            margin-top: 18px;
            padding: 10px;
            background: #fafafa;
            border-left: 3px solid #ccc;
            font-size: 9.5pt;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #888;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }

        .storno-watermark {
            position: fixed;
            top: 40%;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 90pt;
            font-weight: bold;
            color: rgba(200, 0, 0, 0.18);
            transform: rotate(-30deg);
            z-index: 999;
        }
    </style>
</head>
<body>

<?php if ($isCancelled): ?>
    <div class="storno-watermark">STORNO</div>
<?php endif; ?>

<!-- HLAVIČKA -->
<div class="header">
    <table>
        <tr>
            <td class="supplier">
                <div class="name">
                    <?= e((string) ($supplier['official_name'] ?? '')) ?>
                </div>

                <?php if ($supplierStreet !== ''): ?>
                    <div class="line"><?= e($supplierStreet) ?></div>
                <?php endif; ?>

                <?php if ($supplierCity !== ''): ?>
                    <div class="line"><?= e($supplierCity) ?></div>
                <?php endif; ?>

                <?php if ((string) ($supplier['ico'] ?? '') !== ''): ?>
                    <div class="line">IČO: <?= e((string) $supplier['ico']) ?></div>
                <?php endif; ?>

                <?php if ((string) ($supplier['dic'] ?? '') !== ''): ?>
                    <div class="line">DIČ: <?= e((string) $supplier['dic']) ?></div>
                <?php endif; ?>
            </td>

            <td class="invoice-title">
                <div class="label">FAKTURA</div>
                <div class="number">č. <?= e($invoiceNumber) ?></div>

                <div class="meta">
                    <div class="row">
                        <span class="label">Datum vystavení:</span>
                        <?= e($formatDate($issuedAt)) ?>
                    </div>
                    <div class="row">
                        <span class="label">Datum splatnosti:</span>
                        <?= e($formatDate($dueDate)) ?>
                    </div>
                    <div class="row">
                        <span class="label">Forma úhrady:</span>
                        Převodem
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- ODBĚRATEL -->
<div class="parties">
    <table>
        <tr>
            <td>
                <div class="label">Odběratel</div>
                <div class="name">
                    <?= e((string) ($customer['official_name'] ?? '')) ?>
                </div>

                <?php if ($customerStreet !== ''): ?>
                    <div class="line"><?= e($customerStreet) ?></div>
                <?php endif; ?>

                <?php if ($customerCity !== ''): ?>
                    <div class="line"><?= e($customerCity) ?></div>
                <?php endif; ?>

                <?php if ((string) ($customer['ico'] ?? '') !== ''): ?>
                    <div class="line">IČO: <?= e((string) $customer['ico']) ?></div>
                <?php endif; ?>

                <?php if ((string) ($customer['dic'] ?? '') !== ''): ?>
                    <div class="line">DIČ: <?= e((string) $customer['dic']) ?></div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<!-- POLOŽKY -->
<table class="lines">
    <thead>
        <tr>
            <th>Popis</th>
            <th class="num">Množství</th>
            <th>Jednotka</th>
            <th class="num">Cena/jedn.</th>
            <th class="num">Celkem</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lines as $line): ?>
            <?php
            $unit = (string) ($line['unit'] ?? '');
            $qty  = (float) ($line['quantity'] ?? 0);

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
                <td class="num"><?= e($qtyLabel) ?></td>
                <td><?= e($unit) ?></td>
                <td class="num">
                    <?= e(number_format(
                        (float) ($line['unit_price'] ?? 0),
                        2,
                        ',',
                        ' '
                    )) ?>
                </td>
                <td class="num">
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
            <td colspan="4" style="text-align:right;">
                Celkem k úhradě:
            </td>
            <td class="num total">
                <?= e(number_format($total, 2, ',', ' ')) ?> CZK
            </td>
        </tr>
    </tfoot>
</table>

<!-- PLATEBNÍ ÚDAJE + QR -->
<div class="payment">
    <table>
        <tr>
            <td>
                <div class="label">Platební údaje</div>

                <?php if ($accountText !== ''): ?>
                    <div class="line">
                        <strong>Číslo účtu:</strong>
                        <?= e($accountText) ?>
                    </div>
                <?php endif; ?>

                <?php if ((string) ($defaultAccount['iban'] ?? '') !== ''): ?>
                    <div class="line">
                        <strong>IBAN:</strong>
                        <?= e((string) $defaultAccount['iban']) ?>
                    </div>
                <?php endif; ?>

                <div class="line">
                    <strong>Variabilní symbol:</strong>
                    <?= e($invoiceNumber) ?>
                </div>

                <div class="line">
                    <strong>Splatnost:</strong>
                    <?= e($formatDate($dueDate)) ?>
                </div>
            </td>

            <?php if ($qrHtml !== ''): ?>
                <td class="qr">
                    <div class="label">QR Platba</div>
                    <?= $qrHtml ?>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>

<?php if ((string) ($data['note'] ?? '') !== ''): ?>
    <div class="note">
        <strong>Poznámka:</strong><br>
        <?= nl2br(e((string) $data['note'])) ?>
    </div>
<?php endif; ?>

<!-- PATIČKA -->
<div class="footer">
    Nejsem plátce DPH.
    <?php if ((string) ($supplier['official_name'] ?? '') !== ''): ?>
        &nbsp;|&nbsp;
        <?= e((string) $supplier['official_name']) ?>
        <?php if ((string) ($supplier['ico'] ?? '') !== ''): ?>
            , IČO: <?= e((string) $supplier['ico']) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>