<?php
declare(strict_types=1);

namespace App\Helpers;

final class TimeHelper
{
    /**
     * Formátuje počet minut na čitelný časový údaj.
     * Používá české zkratky "h" a "min".
     * TimeHelper::formatMinutes($p['minutes_spent'])
     *
     * @param int $minutes Počet minut
     * @return string Formátovaný čas (např. "2 h 30 min" nebo "0 h")
     */
    public static function formatMinutes(int $minutes): string
    {
        if ($minutes === 0) {
            return '0 h';
        }

        $sign = $minutes < 0 ? '-' : '';
        $minutes = abs($minutes);

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return $m === 0
            ? "{$sign}{$h} h"
            : "{$sign}{$h} h {$m} min";
    }
}