<?php
declare(strict_types=1);

namespace App\Services\Invoice;

use PDO;
use RuntimeException;

final class InvoiceNumberService
{
    /**
     * Přidělí další číslo faktury pro firmu a rok.
     *
     * Číselná řada je určena dvojicí company_id + year.
     * Start je pouze minimální první číslo řady.
     *
     * @param PDO $db
     * @param int $companyId
     * @param int $year
     * @param int $start
     * @return int
     */
    public function nextNumber(
        PDO $db,
        int $companyId,
        int $year,
        int $start
    ): int {
        if ($companyId <= 0) {
            throw new RuntimeException('Invalid company ID.');
        }

        if ($year < 2000 || $year > 2100) {
            throw new RuntimeException('Invalid invoice year.');
        }

        if ($start < 1) {
            throw new RuntimeException('Invoice number start must be greater than zero.');
        }

        /*
         * SELECT ... FOR UPDATE je důležitý:
         * čteme aktuální stav číselné řady uvnitř stejné transakce,
         * ve které následně fakturu vytvoříme.
         */
        $stmt = $db->prepare(
            'SELECT MAX(number)
             FROM internal_invoices
             WHERE company_id = :company_id
               AND year = :year
             FOR UPDATE'
        );

        $stmt->execute([
            'company_id' => $companyId,
            'year'       => $year,
        ]);

        $max = $stmt->fetchColumn();

        $lastNumber = $max === false || $max === null
            ? $start - 1
            : (int) $max;

        $next = $lastNumber + 1;

        if ($next < $start) {
            $next = $start;
        }

        return $next;
    }


/**
 * Naformátuje číslo faktury podle nastaveného formátu.
 *
 * Podporované formáty:
 *   {R3}    rok + číslo na minimálně 3 místa
 *   {RM3}   rok + měsíc + číslo na minimálně 3 místa
 *   {R5}    rok + číslo na minimálně 5 míst
 *   {5}     pouze číslo na minimálně 5 míst
 *   FA{R3}  FA + rok + číslo
 *   FA{RM3} FA + rok + měsíc + číslo
 *   FA{R5}  FA + rok + číslo
 *   FA{5}   FA + číslo
 *
 * Rozsah šířky číselné části je 3 až 10 míst.
 *
 * @param int $year
 * @param int $month
 * @param int $number
 * @param string $format
 * @return string
 */
public function format(
    int $year,
    int $month,
    int $number,
    string $format
): string {
    if ($number < 1) {
        throw new RuntimeException('Invalid invoice number.');
    }

    if ($month < 1 || $month > 12) {
        throw new RuntimeException('Invalid invoice month.');
    }

    if ($format === '') {
        throw new RuntimeException('Invoice number format is empty.');
    }

    $result = preg_replace_callback(
        '/^(FA)?\{(RM|R)(\d{1,2})\}$/',
        static function (array $matches) use (
            $year,
            $month,
            $number
        ): string {
            $prefix = $matches[1];
            $type = $matches[2];
            $length = (int) $matches[3];

            self::validateNumberLength($length);

            $numberPart = str_pad(
                (string) $number,
                $length,
                '0',
                STR_PAD_LEFT
            );

            return $type === 'RM'
                ? sprintf(
                    '%s%04d%02d%s',
                    $prefix,
                    $year,
                    $month,
                    $numberPart
                )
                : sprintf(
                    '%s%04d%s',
                    $prefix,
                    $year,
                    $numberPart
                );
        },
        $format
    );

    if ($result !== null && $result !== $format) {
        return $result;
    }

    $result = preg_replace_callback(
        '/^(FA)?\{(\d{1,2})\}$/',
        static function (array $matches) use ($number): string {
            $prefix = $matches[1];
            $length = (int) $matches[2];

            self::validateNumberLength($length);

            return $prefix . str_pad(
                (string) $number,
                $length,
                '0',
                STR_PAD_LEFT
            );
        },
        $format
    );

    if ($result !== null && $result !== $format) {
        return $result;
    }

    return $this->formatCustom(
        $year,
        $month,
        $number,
        $format
    );
}

/**
 * Zpracuje vlastní formát.
 *
 * Podporované zástupné symboly:
 *   {R}  = rok
 *   {M}  = měsíc
 *   {N}  = číslo
 *   {N3} až {N10} = číslo s minimální šířkou
 *
 * @param int $year
 * @param int $month
 * @param int $number
 * @param string $format
 * @return string
 */
private function formatCustom(
    int $year,
    int $month,
    int $number,
    string $format
): string {
    $result = str_replace(
        ['{R}', '{M}', '{N}'],
        [
            (string) $year,
            sprintf('%02d', $month),
            (string) $number,
        ],
        $format
    );

    $result = preg_replace_callback(
        '/\{N(\d{1,2})\}/',
        static function (array $matches) use ($number): string {
            $length = (int) $matches[1];

            self::validateNumberLength($length);

            return str_pad(
                (string) $number,
                $length,
                '0',
                STR_PAD_LEFT
            );
        },
        $result
    );

    if ($result === null || $result === '') {
        throw new RuntimeException(
            'Invalid invoice number format.'
        );
    }

    return $result;
}

/**
 * Ověří povolenou šířku číselné části.
 *
 * @param int $length
 * @return void
 */
private static function validateNumberLength(int $length): void
{
    if ($length < 3 || $length > 10) {
        throw new RuntimeException(
            'Invoice number length must be between 3 and 10 digits.'
        );
    }
}


/**
 * Ověří, zda je formát čísla faktury platný.
 *
 * Validace používá stejná pravidla jako samotné formátování.
 *
 * @param string $format
 * @return bool
 */
public function isValidFormat(string $format): bool
{
    $format = trim($format);

    if ($format === '') {
        return false;
    }

    try {
        $this->format(
            2026,
            1,
            1,
            $format
        );

        return true;
    } catch (RuntimeException) {
        return false;
    }
}


}
