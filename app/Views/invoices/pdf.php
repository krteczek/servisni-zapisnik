<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


$pdfData = $view->pdfData;

$invoice  = $pdfData['invoice'];
$supplier = $pdfData['supplier'];
$customer = $pdfData['customer'];
$lines    = $pdfData['lines'];
$totals   = $pdfData['totals'];
$payment  = $pdfData['payment'];
$note     = $pdfData['note'];

?><!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Faktura <?= e($invoice['number']) ?></title>

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

<?php if ($invoice['is_cancelled']): ?>
    <div class="storno-watermark">STORNO</div>
<?php endif; ?>

<!-- HLAVIČKA -->
<div class="header">
    <table>
        <tr>
            <td class="supplier">
                <div class="name">
                    <?= e($supplier['name']) ?>
                </div>

                <?php if ($supplier['street'] !== ''): ?>
                    <div class="line"><?= e($supplier['street']) ?></div>
                <?php endif; ?>

                <?php if ($supplier['city'] !== ''): ?>
                    <div class="line"><?= e($supplier['city']) ?></div>
                <?php endif; ?>

                <?php if ($supplier['ico'] !== ''): ?>
                    <div class="line">IČO: <?= e($supplier['ico']) ?></div>
                <?php endif; ?>

                <?php if ($supplier['dic'] !== ''): ?>
                    <div class="line">DIČ: <?= e($supplier['dic']) ?></div>
                <?php endif; ?>
            </td>

            <td class="invoice-title">
                <div class="label">FAKTURA</div>
                <div class="number">č. <?= e($invoice['number']) ?></div>





                <div class="meta">
                    <div class="row">
                        <span class="label">Datum vystavení:</span>
                        <?= e($invoice['issued_at_formatted']) ?>
                    </div>
                    <div class="row">
                        <span class="label">Datum splatnosti:</span>
                        <?= e($invoice['due_date_formatted']) ?>
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
                        <?= e($customer['name']) ?>
                    </div>

                    <?php if ($customer['street'] !== ''): ?>
                        <div class="line"><?= e($customer['street']) ?></div>
                    <?php endif; ?>

                    <?php if ($customer['city'] !== ''): ?>
                        <div class="line"><?= e($customer['city']) ?></div>
                    <?php endif; ?>

                    <?php if ($customer['ico'] !== ''): ?>
                        <div class="line">IČO: <?= e($customer['ico']) ?></div>
                    <?php endif; ?>

                    <?php if ($customer['dic'] !== ''): ?>
                        <div class="line">DIČ: <?= e($customer['dic']) ?></div>
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
            <tr>
                <td><?= e($line['description']) ?></td>
                <td class="num"><?= e($line['quantity_label']) ?></td>
                <td><?= e($line['unit']) ?></td>
                <td class="num"><?= e($line['unit_price']) ?></td>
                <td class="num">
                    <?= e($line['total']) ?>
                    <?= e($line['currency']) ?>
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
                <?= e($totals['total_formatted']) ?>
                <?= e($totals['currency']) ?>
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

                <?php if ($payment['account'] !== ''): ?>
                    <div class="line">
                        <strong>Číslo účtu:</strong>
                        <?= e($payment['account']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($payment['iban'] !== ''): ?>
                    <div class="line">
                        <strong>IBAN:</strong>
                        <?= e($payment['iban']) ?>
                    </div>
                <?php endif; ?>

                <div class="line">
                    <strong>Variabilní symbol:</strong>
                    <?= e($payment['variable_symbol']) ?>
                </div>

                <div class="line">
                    <strong>Splatnost:</strong>
                    <?= e($payment['due_date']) ?>
                </div>
            </td>

            <?php if ($payment['qr_html'] !== ''): ?>
                <td class="qr">
                    <div class="label">QR Platba</div>
                    <?= $payment['qr_html'] ?>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>
<?php if ($note !== ''): ?>
    <div class="note">
        <strong>Poznámka:</strong><br>
        <?= nl2br(e($note)) ?>
    </div>
<?php endif; ?>
<!-- PATIČKA -->
<div class="footer">
    Nejsem plátce DPH.
    <?php if ($supplier['name'] !== ''): ?>
        &nbsp;|&nbsp;
        <?= e($supplier['name']) ?>
        <?php if ($supplier['ico'] !== ''): ?>
            , IČO: <?= e($supplier['ico']) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>