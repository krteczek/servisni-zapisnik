<?php
declare(strict_types=1);

use App\Core\Types;

/**
 * Bezpečně escapuje text pro výstup do HTML.
 * Zabrání XSS útoku při zobrazování uživatelského vstupu.
 *
 * TODO: [SECURITY] Zvážit použití HTML Purifier pro povolené HTML tagy
 * TODO: [PERFORMANCE] Přidat caching pro často escapované identické texty
 *
 * @param mixed $value Text k escapování
 * @return string Escapovaný text (prázdný string pokud vstup je null)
 */
function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }

    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE,
        'UTF-8'
    );
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

    $sign = $minutes < 0 ? '-' : '';
    $minutes = abs($minutes);

    $h = intdiv($minutes, 60);
    $m = $minutes % 60;

    return $m === 0
        ? "{$sign}{$h} h"
        : "{$sign}{$h} h {$m} min";
}

/**
 * Formátuje datetime na český dlouhý formát s měsícem v genitivu.
 * Vrací ve formátu "1. ledna 2023 14:30".
 *
 * TODO: [I18N] Přidat podporu pro další jazyky (anglické, německé měsíce)
 * TODO: [FEATURE] Přidat volbu zahrnutí/vyloučení času
 *
 * @param string|null $datetime DateTime string (musí být parsovatelný PHP DateTime)
 * @param bool $withTime Zda má vrátit i čas
 * @return string Formátované české datum (s časem, pokud je $withTime true)
 */
function formatCzDate(?string $datetime, bool $withTime = false): string
{
    if ($datetime === null || $datetime === "") {
        return 'neuvedeno';
    }

    try {
        $dt = new DateTime($datetime);
    } catch (Throwable) {
        return '<span title="Neplatné datum">neuvedeno</span>';
    }

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

    $day   = (int) $dt->format('j');
    $month = (int) $dt->format('n');
    $year  = $dt->format('Y');

    return sprintf(
        ($withTime
        ?    '%d. %s %s %s'
        :    '%d. %s %s'),
        $day,
        $months[$month],
        $year,
        ($withTime ? $dt->format('H:i') : '')
    );
}

/**
 * @param mixed $key
 * @return string
 */
function t(mixed $key): string
{
    if (!is_string($key)) {
        return e((string)$key);
    }

    $statuses = [
        'new'         => 'Nová',
        'in_progress' => 'Probíhá',
        'done'        => 'Hotovo',
        'cancelled'   => 'Zrušeno',
        'open'        => 'Otevřeno',
        'normal'      => 'Normální',
        'high'        => 'Vysoká',
        'emergency'   => 'Naléhavé',
        'low'         => 'Nízká',
        'mistr'       => 'Mistr',
        'admin'       => 'Admin',
        'predak'      => 'Předák',
        'monter'      => 'Montér',
        'active'      => 'Aktivní',
        'inactive'    => 'Neaktivní',
        'pending'     => 'Čeká na aktivaci',
        'phone'       => 'Telefón',
        'email'       => 'Email',
        'personal'    => 'Osobně',
        'exported'    => 'Exportováno',
    ];

    $key = strtolower(trim($key));

    return e($statuses[$key] ?? $key);
}

/**
 * @param mixed $key
 * @return string
 */
function te(mixed $key): string
{
    return e(t($key));
}

/**
 * Zjistí IP adresu klienta.
 *
 * @return string
 */
function getClientIP(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    return filter_var($ip, FILTER_VALIDATE_IP)
        ? $ip
        : '0.0.0.0';
}

/**
 * @param string $ip
 * @return string
 */
function ipToBinary(string $ip): string
{
    $binary = @inet_pton($ip);

    return $binary !== false ? $binary : (string) inet_pton('0.0.0.0');
}

/**
 * Zjistí user agenta klienta.
 *
 * @return string
 */
function getClientUserAgent(): string
{
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return 'Unknown';
    }

    return mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
}

/**
 * Vrátí stav uživatele (active/inactive/pending) podle jeho dat.
 *
 * @param array<string, mixed> $user
 * @return string
 */
function active(array $user): string
{
    $passwordHash = $user['password_hash'] ?? null;
    $activeFlag   = isset($user['active']) ? (int)$user['active'] : 0;

    if ($passwordHash === null && $activeFlag === 0) {
        return 'pending';
    }

    if ($activeFlag === 0) {
        return 'inactive';
    }

    return 'active';
}

/**
 * Alias pro active().
 *
 * @param array<string, mixed> $user
 * @return string
 */
function a(array $user): string
{
    return active($user);
}

/**
 * Zpracuje text přes Texy.
 *
 * @param string|int|float|bool|null $text
 * @return string
 */
function tx(string|int|float|bool|null $text): string
{
    $text = (string) $text;
    if ($text === '') {
        return '';
    }
    static $texy = null;

    if ($texy === null) {
        $texy = new \Texy();
        Texy\Configurator::safeMode($texy);
    }

    return $texy->process($text);
}

/**
 * Vrátí 'checked' pokud je hodnota truthy.
 *
 * @param mixed $value
 * @return string
 */
function checked(mixed $value): string
{
    return (bool) $value ? 'checked' : '';
}

/**
 * Vrátí deadline class a text pro dané datum.
 *
 * @param string|null $dueDate Datum ve formátu date
 * @return array{0: string, 1: string}
 */
function deadlineDateHelper(?string $dueDate): array
{
    $deadlineClass = 'deadline-none';
    $deadlineText  = 'Neuvedeno';

    if ($dueDate !== null && $dueDate !== '') {
        $timestamp = strtotime($dueDate);

        if ($timestamp === false) {
            return [$deadlineClass, $deadlineText];
        }

        $today = date('Y-m-d');
        $due   = date('Y-m-d', $timestamp);

        $deadlineText = date('d.m.Y', $timestamp);

        if ($due < $today) {
            $deadlineClass = 'deadline-late';
        } elseif ($due === $today) {
            $deadlineClass = 'deadline-today';
        } else {
            $deadlineClass = 'deadline-future';
        }
    }

    return [$deadlineClass, $deadlineText];
}

/**
 * Dump and die.
 *
 * @param mixed ...$vars
 * @return never
 */
function dd(mixed ...$vars): never
{
    echo '<pre style="background:#111;color:#0f0;padding:15px;">';

    foreach ($vars as $var) {
        var_dump($var);
        echo "\n";
    }

    echo '</pre>';

    die(1);
}

/**
 * Dump and continue.
 *
 * @param mixed ...$vars
 * @return void
 */
function dc(mixed ...$vars): void
{
    echo '<pre style="background:#111;color:#0f0;padding:15px;">';

    foreach ($vars as $var) {
        var_dump($var);
        echo "\n";
    }

    echo '</pre>';
}

/**
 * Vrátí unikátní uživatelská jména z participantů.
 *
 * @param array<int, array{user_id?: int, first_name?: string, last_name?: string}> $participants
 * @return array<int, string>
 */
function uniqueUsernames(array $participants): array
{
    $userNames = [];

    foreach ($participants as $participant) {
        $userId = $participant['user_id'] ?? 0;

        if ($userId === 0) {
            continue;
        }

        $userNames[$userId] = trim(
            ($participant['first_name'] ?? '') . ' ' .
            ($participant['last_name'] ?? '')
        );
    }

    return $userNames;
}

/**
 * Debugovací funkce pro zobrazení struktury ViewContext ve view.
 *
 * @param object $view Objekt view (typicky ViewContext)
 * @param bool $detailed Zda zobrazit detailní rozbor i pro prázdné array
 * @return void
 */
function debugViewVariables(object $view, bool $detailed = false): void
{
    echo "<pre style='background:#111;color:#0f0;padding:15px;font-size:12px;'>";
    echo "=== ALL VIEW PROPERTIES ===\n\n";

    foreach (get_object_vars($view) as $key => $value) {
        if ($value instanceof \App\Core\Router) {
            echo "=== $key ===\n";
            echo "  type: " . get_debug_type($value) . " (skipped, contains cycles)\n\n";
            continue;
        }

        echo "=== $key ===\n";
        echo "  type: " . get_debug_type($value);

        if (is_array($value)) {
            echo ", count: " . count($value);

            if ($value === []) {
                echo " (empty)\n\n";
                continue;
            }

            echo "\n";

            if (array_is_list($value)) {
                echo "  is_list: YES\n";
                $first = $value[0];

                if (is_array($first)) {
                    echo "  first item keys + types:\n";
                    foreach ($first as $k => $v) {
                        echo "    $k: " . get_debug_type($v) . "\n";
                    }
                } else {
                    echo "  first item type: " . get_debug_type($first) . "\n";
                }
            } else {
                echo "  is_list: NO\n";
                echo "  keys + types:\n";
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        echo "    $k: array (count: " . count($v) . ")\n";
                    } else {
                        echo "    $k: " . get_debug_type($v) . "\n";
                    }
                }
            }
        } else {
            echo "\n";
        }

        echo "\n";
    }

    echo "</pre>";
}