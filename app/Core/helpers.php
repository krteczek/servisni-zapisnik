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
