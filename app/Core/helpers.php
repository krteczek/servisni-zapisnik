<?php
declare(strict_types=1);

function e(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}


function formatValue(mixed $v): string {
    if ($v === null) return '<em>null</em>';
    if ($v === true || $v === 1) return '✔ ano';
    if ($v === false || $v === 0) return '✖ ne';
    return e((string)$v);
}
//formátuje čas
function formatMinutes(int $minutes): string
{
    if ($minutes === 0) {
        return '0 h';
    }

    $h = intdiv($minutes, 60);
    $m = $minutes % 60;

    return $m === 0
        ? "{$h} h"
        : "{$h} h {$m} min";
}

function formatCzDate(string $datetime): string
{
$months = [
    1 => 'ledna',
    2 => 'února',
    3 => 'března',
    4 => 'dubna',
    5 => 'května',
    6 => 'června',
    7 => 'července',
    8 => 'srpna',
    9 => 'září',
    10 => 'října',
    11 => 'listopadu',
    12 => 'prosince',
];

    $dt = new DateTime($datetime);

    $day   = (int) $dt->format('j');
    $month = (int) $dt->format('n');
    $year  = $dt->format('Y');

    //return $day . '. ' . $months[$month] . ' ' . $year;
    return sprintf(
    '%d. %s %s %s',
    $day,
    $months[$month],
    $year,
    $dt->format('H:i')
);
}
