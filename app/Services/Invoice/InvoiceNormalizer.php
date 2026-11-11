<?php

declare(strict_types=1);

namespace App\Services\Invoice;

final class InvoiceNormalizer
{
    /**
     * Převede validovaná data faktury do struktury
     * určené pro uložení.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array
    {
        /** @var array<int, array<string, mixed>> $lines */
        $lines = [];

        $rawLines = $data['lines'] ?? [];

        if (is_array($rawLines)) {
            foreach ($rawLines as $line) {
                if (!is_array($line)) {
                    continue;
                }

                $lines[] = $this->normalizeLine($line);
            }
        }

        return [
            'title' => (string) ($data['title'] ?? ''),
            'issued_at' => (string) ($data['issued_at'] ?? ''),
            'due_date' => (string) ($data['due_date'] ?? ''),
            'note' => (string) ($data['note'] ?? ''),
            'contact_id' => (int) ($data['contact_id'] ?? 0),
            'save_customer' => (bool) (
                $data['save_customer'] ?? false
            ),
            'customer' => $data['customer'] ?? [],
            'tasks' => $data['tasks'] ?? [],
            'lines' => $lines,
        ];
    }

    /**
     * Normalizuje jeden fakturační řádek.
     *
     * `quantity` je vždy authoritative hodnota.
     * Pro unit='min' jsou to minuty (celé číslo),
     * pro ostatní jednotky obecné množství.
     *
     * hours/minutes z formuláře jsou pouze vstupní
     * reprezentace a převádí je InvoiceValidator.
     *
     * @param array<string, mixed> $line
     * @return array<string, mixed>
     */
    private function normalizeLine(array $line): array
    {
        $unit = trim((string) ($line['unit'] ?? ''));

        $quantity = max(
            0.0,
            (float) ($line['quantity'] ?? 0)
        );

        $priceUnit = trim(
            (string) ($line['price_unit'] ?? '')
        );

        $unitPrice = round(
            (float) ($line['unit_price'] ?? 0),
            2
        );

        $currency = strtoupper(
            trim((string) ($line['currency'] ?? 'CZK'))
        );

        $total = $this->calculateTotal(
            $quantity,
            $unit,
            $priceUnit,
            $unitPrice
        );

        return [
            'description' => trim(
                (string) ($line['description'] ?? '')
            ),
            'quantity' => $quantity,
            'unit' => $unit,
            'price_unit' => $priceUnit,
            'unit_price' => $unitPrice,
            'currency' => $currency,
            'total' => $total,
        ];
    }

    /**
     * Vypočítá celkovou cenu řádku.
     *
     * U minut se cena počítá jako poměrná část hodinové sazby.
     */
    private function calculateTotal(
        float $quantity,
        string $unit,
        string $priceUnit,
        float $unitPrice
    ): float {
        if (
            $unit === 'min'
            && $priceUnit === 'hod'
        ) {
            return round(
                ($quantity / 60) * $unitPrice,
                2
            );
        }

        return round(
            $quantity * $unitPrice,
            2
        );
    }
}