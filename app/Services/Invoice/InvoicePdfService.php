<?php
declare(strict_types=1);

namespace App\Services\Invoice;


use Defr\QRPlatba\QRPlatba;
use Throwable;
use App\Core\LoggerHolder;

final class InvoicePdfService
{
    /**
     * Připraví kompletní data pro vykreslení PDF faktury.
     *
     * Bez databázových dotazů.
     *
     * @param array<string, mixed> $invoice
     * @param array<string, mixed> $supplier
     * @return array<string, mixed>
     */
    public function prepare(
        array $invoice,
        array $supplier
    ): array {

        $data      = $invoice['invoice'] ?? [];
        $customer  = $invoice['customer'] ?? [];
        $lines     = $invoice['lines'] ?? [];

        $invoiceNumber = (string) ($invoice['invoice_number'] ?? '');
        $issuedAt      = (string) ($invoice['issued_at'] ?? '');
        $dueDate       = (string) ($invoice['due_date'] ?? '');
        $status        = (string) ($invoice['status'] ?? '');

        /*
         * Součet položek.
         */
        $total = 0.0;

        foreach ($lines as $line) {
            $total += (float) ($line['total'] ?? 0);
        }

        /*
         * Dodavatel a odběratel.
         */
        $supplierData = $this->prepareParty($supplier);
        $customerData = $this->prepareParty($customer);

        /*
         * Fakturační položky.
         */
        $preparedLines = [];

        foreach ($lines as $line) {
            $unit = (string) ($line['unit'] ?? '');
            $quantity = (float) ($line['quantity'] ?? 0);

            $preparedLines[] = [
                'description' => (string) ($line['description'] ?? ''),
                'quantity' => $quantity,
                'quantity_label' => $this->formatQuantity(
                    $quantity,
                    $unit
                ),
                'unit' => $unit,
                'price_unit' => (string) ($line['price_unit'] ?? ''),
                'unit_price' => number_format(
                    (float) ($line['unit_price'] ?? 0),
                    2,
                    ',',
                    ' '
                ),
                'total' => number_format(
                    (float) ($line['total'] ?? 0),
                    2,
                    ',',
                    ' '
                ),
                'currency' => (string) ($line['currency'] ?? 'CZK'),
            ];
        }

        /*
         * Platební údaje.
         */
        $payment = $this->preparePayment(
            $supplier,
            $invoiceNumber,
            $dueDate,
            $total
        );

        /*
         * Výsledná datová struktura.
         */
        return [
            'invoice' => [
                'id' => (int) ($invoice['id'] ?? 0),
                'number' => $invoiceNumber,
                'status' => $status,
                'is_cancelled' => InvoiceStatus::isCancelled($status),
                'issued_at' => $issuedAt,
                'issued_at_formatted' => $this->formatDate($issuedAt),
                'due_date' => $dueDate,
                'due_date_formatted' => $this->formatDate($dueDate),
            ],

            'supplier' => $supplierData,

            'customer' => $customerData,

            'lines' => $preparedLines,

            'totals' => [
                'total' => $total,
                'total_formatted' => number_format(
                    $total,
                    2,
                    ',',
                    ' '
                ),
                'currency' => (string) (
                    $lines[0]['currency'] ?? 'CZK'
                ),
            ],

            'payment' => $payment,

            'note' => (string) ($data['note'] ?? ''),
        ];
    }

    /**
     * Připraví údaje obchodní strany.
     *
     * @param array<string, mixed> $party
     * @return array<string, string>
     */
    private function prepareParty(array $party): array
    {
        $street = trim(
            (string) ($party['street'] ?? '') . ' ' .
            (string) ($party['house_number'] ?? '') .
            (
                (string) ($party['orientation_number'] ?? '') !== ''
                    ? '/' . $party['orientation_number']
                    : ''
            )
        );

        $city = trim(
            (string) ($party['postal_code'] ?? '') . ' ' .
            (string) ($party['city'] ?? '')
        );

        return [
            'name' => (string) ($party['official_name'] ?? ''),
            'street' => $street,
            'city' => $city,
            'ico' => (string) ($party['ico'] ?? ''),
            'dic' => (string) ($party['dic'] ?? ''),
        ];
    }

    /**
     * Připraví bankovní údaje a QR platbu.
     *
     * @param array<string, mixed> $supplier
     * @return array<string, string>
     */
    private function preparePayment(
        array $supplier,
        string $invoiceNumber,
        string $dueDate,
        float $total
    ): array {

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
         * Číslo účtu.
         */
        $accountText = '';

        if (is_array($defaultAccount)) {
            $parts = [];

            if (($defaultAccount['account_prefix'] ?? '') !== '') {
                $parts[] = (string) $defaultAccount['account_prefix'];
            }

            if (($defaultAccount['account_number'] ?? '') !== '') {
                $parts[] = (string) $defaultAccount['account_number'];
            }

            $accountText = implode('-', $parts);

            if (($defaultAccount['bank_code'] ?? '') !== '') {
                $accountText .= '/' . $defaultAccount['bank_code'];
            }
        }

        $iban = is_array($defaultAccount)
            ? (string) ($defaultAccount['iban'] ?? '')
            : '';

        /*
         * QR Platba.
         */
        $qrHtml = '';

        if ($defaultAccount !== null && $total > 0) {
            try {
                $qrPlatba = new QRPlatba();

                if ($iban !== '') {
                    $qrPlatba->setIBAN($iban);
                } elseif ($accountText !== '') {
                    $qrPlatba->setAccount($accountText);
                }

                $qrPlatba
                    ->setVariableSymbol($invoiceNumber)
                    ->setAmount($total)
                    ->setMessage('Faktura ' . $invoiceNumber);

                $date = $this->parseDate($dueDate);

                if ($date !== null) {
                    $qrPlatba->setDueDate($date);
                }

                $qrHtml = $qrPlatba->getQRCodeImage();

            } catch (Throwable $e) {
                // QR platba je volitelná.
            LoggerHolder::get()->error(
                'InvoicePdfService.preparePayment: failed',
                [
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'trace'     => $e->getTraceAsString(),

                ]
            );

            }
        }

        return [
            'account' => $accountText,
            'iban' => $iban,
            'variable_symbol' => $invoiceNumber,
            'due_date' => $this->formatDate($dueDate),
            'qr_html' => $qrHtml,
        ];
    }

    /**
     * Formátuje množství.
     */
    private function formatQuantity(
        float $quantity,
        string $unit
    ): string {

        if ($unit === 'min') {
            $minutes = (int) $quantity;

            $hours = intdiv($minutes, 60);
            $rest = $minutes % 60;

            $result = trim(
                ($hours > 0 ? $hours . ' h' : '') .
                ($rest > 0 ? ' ' . $rest . ' min' : '')
            );

            return $result !== '' ? $result : '0 min';
        }

        return (string) $quantity;
    }

    /**
     * Formátuje datum Y-m-d na d.m.Y.
     */
    private function formatDate(string $date): string
    {
        $parsed = $this->parseDate($date);

        return $parsed !== null
            ? $parsed->format('d.m.Y')
            : $date;
    }

    /**
     * Bezpečně načte datum.
     */
    private function parseDate(string $date): ?\DateTime
    {
        if ($date === '') {
            return null;
        }

        $parsed = \DateTime::createFromFormat('!Y-m-d', $date);

        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            return null;
        }

        return $parsed;
    }

}
