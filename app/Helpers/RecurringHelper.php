<?php
declare(strict_types=1);

namespace App\Helpers;

final class RecurringHelper
{
    public static function describe(array $recurring): string
    {
        $type = $recurring['frequency_type'] ?? '';
        $value = (int)($recurring['frequency_value'] ?? 1);

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


    public static function warningText(array $recurring): string
    {
        $days = (int)($recurring['warning_days_before'] ?? 0);

        return match ($days) {
            0 => 'Bez předstihu',
            1 => '1 den předem',
            default => "{$days} dnů předem",
        };
    }

    public static function nextDueDate(array $recurring): string
    {
        if (empty($recurring['next_due_date'])) {
            return '-';
        }

        return date('d.m.Y', strtotime($recurring['next_due_date']));
    }
}