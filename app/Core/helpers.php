<?php
declare(strict_types=1);

/**
 * Bezpečně escapuje text pro výstup do HTML.
 * Zabrání XSS útoku při zobrazování uživatelského vstupu.
 *
 * TODO: [SECURITY] Zvážit použití HTML Purifier pro povolené HTML tagy
 * TODO: [PERFORMANCE] Přidat caching pro často escapované identické texty
 *
 * @param string|null $text Text k escapování
 * @return string Escapovaný text (prázdný string pokud vstup je null)
 */
function e(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formátuje hodnotu pro zobrazení v uživatelském rozhraní.
 * Speciálně formátuje boolean, null a číselné hodnoty.
 *
 * TODO: [I18N] Přidat lokalizaci pro "ano"/"ne" podle jazyka uživatele
 * TODO: [FEATURE] Přidat formátování pro pole a objekty (json_encode)
 *
 * @param mixed $v Hodnota k formátování
 * @return string Formátovaný a escapovaný výstup
 */
function formatValue(mixed $v): string {
    if ($v === null) return '<em>null</em>';
    if ($v === true || $v === 1) return '✔ ano';
    if ($v === false || $v === 0) return '✖ ne';
    return e((string)$v);
}

/**
 * Formátuje počet minut na čitelný časový údaj (hodiny a minuty).
 * Používá české zkratky "h" a "min".
 *
 * TODO: [I18N] Přidat podporu pro jiné jazyky (hours/minutes)
 * TODO: [FEATURE] Přidat volbu formátu (např. 1.5h místo 1 h 30 min)
 *
 * @param int $minutes Počet minut
 * @return string Formátovaný čas (např. "2 h 30 min" nebo "0 h")
 */
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

/**
 * Formátuje datetime na český dlouhý formát s měsícem v genitivu.
 * Vrací ve formátu "1. ledna 2023 14:30".
 *
 * TODO: [I18N] Přidat podporu pro další jazyky (anglické, německé měsíce)
 * TODO: [PERFORMANCE] Zvážit cachování DateTime objektů pro opakované volání
 * TODO: [FEATURE] Přidat volbu zahrnutí/vyloučení času
 *
 * @param string $datetime DateTime string (musí být parsovatelný PHP DateTime)
 * @return string Formátované české datum s časem
 * @throws \Exception Pokud vstupní string není validní datetime
 */
function formatCzDate(string $datetime): string
{
    // TODO: [MAINTENANCE] Přesunout měsíce do konfigurace nebo separátní třídy
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

    return sprintf(
        '%d. %s %s %s',
        $day,
        $months[$month],
        $year,
        $dt->format('H:i')
    );
}

function t(string $key): string
{
	
	$statuses = [
		'new' 			=> 'Nový',
		'in_progress' 	=> 'Probíhá',
		'done' 			=> 'Hotovo',
		'canceled'		=> 'Zrušeno',
		'open'			=> 'Otevřeno',
		'normal'			=> 'Normální',
		'high'			=> 'Vysoká',
		'emergency'		=> 'Naléhavé',
		'low'				=> 'Nízká',
		'mistr'			=> 'Mistr',
		'admin'			=> 'Admin',
		'predak'			=> 'Předák',
		'monter'			=> 'Montér',
		'active'			=> 'Aktivní',
		'inactive'		=> 'Neaktivní',
		'pending'		=> 'Čeká...',
		
	];
	$key = strtolower(trim($key));
	return e($statuses[$key] ?? $key);
}

function te(string $key): string
{
    return e(t($key));
}
