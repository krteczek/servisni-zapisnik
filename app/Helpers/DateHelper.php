<?php
declare(strict_types=1);

namespace App\Helpers;

use DateTimeImmutable;

final class DateHelper
{
    /**
     * Vždy vrací správný formát data
     * @param null|string $value datum třeba z formuláře
     * @param int $days o kolik se posune vrácené datum, fungují i záporné hodnoty. 0 je dnes
     * @return string bezpečné datum použitelné při ukládání doDB
     */
    public static function normalizeDate(
        ?string $value,
        int $days = 0,
    ): string {

        $value = trim((string) $value);

        if ($value !== '') {

            $ts = strtotime($value);

            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        return (new DateTimeImmutable())
            ->modify($days . ' days')
            ->format('Y-m-d');
    }
    /**
     * Ověří správmost zadaného datumu
     * @param null|string $date datum třeba z formuláře
     * @return bool
     */

public static function isValidDate(?string $date): bool
{
    $date = trim((string) $date);

    if ($date === '') {
        return false;
    }

    $dt = DateTimeImmutable::createFromFormat(
        'Y-m-d',
        $date
    );

    return $dt !== false
        && $dt->format('Y-m-d') === $date;
}



public static function parseDate(?string $date): ?string
{
    $date = trim((string) $date);

    if (!self::isValidDate($date)) {
        return null;
    }

    return $date;
}
}