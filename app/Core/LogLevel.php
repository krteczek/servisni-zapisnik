<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Konstanty pro úrovně logování podle PSR-3 standardu.
 * Definuje pojmenované konstanty pro všech 8 úrovní logování.
 *
 * Použití konstant místo stringů zajišťuje typovou bezpečnost
 * a prevenci překlepů v názvech úrovní logování.
 *
 * @link https://www.php-fig.org/psr/psr-3/#5-psrlogloglevel PSR-3 Log Levels
 */
final class LogLevel
{
    /**
     * Systém je nepoužitelný.
     * Nejvyšší úroveň závažnosti.
     */
    public const EMERGENCY = 'emergency';

    /**
     * Je třeba okamžité akce.
     * Např. celá aplikace je offline, databáze nedostupná.
     */
    public const ALERT = 'alert';

    /**
     * Kritické situace.
     * Např. neočekávaná výjimka, neošetřená chyba aplikace.
     */
    public const CRITICAL = 'critical';

    /**
     * Chyby runtime, které nevyžadují okamžitou akci.
     * Měly by být zaznamenány a monitorovány.
     */
    public const ERROR = 'error';

    /**
     * Výjimečné události, které nejsou chybami.
     * Např. použití zastaralého API, neoptimální kód.
     */
    public const WARNING = 'warning';

    /**
     * Normální, ale významné události.
     * Např. uživatelské akce s vyšší důležitostí.
     */
    public const NOTICE = 'notice';

    /**
     * Zajímavé události.
     * Např. přihlášení uživatele, spuštění cron jobu.
     */
    public const INFO = 'info';

    /**
     * Podrobné informace pro debugging.
     * Nejnižší úroveň závažnosti, pouze pro vývoj.
     */
    public const DEBUG = 'debug';

    // TODO: [MAINTENANCE] Přidat metodu pro získání všech úrovní jako pole
    // TODO: [FEATURE] Přidat metodu pro porovnávání úrovní (je vyšší/nižší)
}