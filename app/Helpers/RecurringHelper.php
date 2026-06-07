<?php
declare(strict_types=1);

namespace App\Helpers;

final class RecurringHelper
{
    public static function describe(string $type, int $value = 1): string
    {
        return match ($type) {

            'daily' => $value === 1
                ? 'Každý den'
                : "Každých {$value} dní",

            'weekly' => $value === 1
                ? 'Každý týden'
                : "Každé {$value} týdny",

            'monthly' => $value === 1
                ? 'Každý měsíc'
                : "Každé {$value} měsíce",

            'yearly' => $value === 1
                ? 'Každý rok'
                : "Každé {$value} roky",

            default => 'Neznámé opakování',
        };
    }


    public static function warningText(int $warningDaysBefore): string
    {
        return match ($warningDaysBefore) {
            0 => 'Bez předstihu',
            1 => '1 den předem',
            default => "{$warningDaysBefore} dnů předem",
        };
    }

    public static function nextDueDate(?string $nextDueDate): string
    {
        if (empty($nextDueDate)) {
            return '-';
        }

        return date('d.m.Y', strtotime($nextDueDate));
    }
}