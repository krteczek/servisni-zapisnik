<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;
use App\Core\LoggerHolder;

/**
 * Globální handler pro nezachycené výjimky v aplikaci.
 * Zajišťuje konzistentní logování a zobrazení chyb uživateli.
 * Rozlišuje mezi vývojovým a produkčním prostředím.
 */
final class ExceptionHandler
{
    /**
     * Registruje tento handler jako globální exception handler.
     * Zachytí všechny výjimky, které nebyly zachyceny try-catch bloky.
     *
     * TODO: [MAINTENANCE] Přidat registraci error handleru pro E_ERROR, E_WARNING atd.
     * TODO: [SECURITY] Zvážit registraci shutdown handleru pro fatální chyby
     */
    public static function register(): void
    {
        set_exception_handler([self::class, 'handle']);
    }

    /**
     * Zpracuje nezachycenou výjimku.
     * Provádí logování, nastaví HTTP status a zobrazí vhodnou chybovou stránku.
     *
     * Vedlejší efekty:
     * - Zapíše chybu do logovacího systému
     * - Nastaví HTTP status code 500
     * - Ukončí vykonávání skriptu (exit)
     *
     * TODO: [OBSERVABILITY] Přidat zaslání chyby do externího monitoringu (Sentry)
     * TODO: [SECURITY] Logovat také $_SERVER, $_REQUEST data pro debugging útoků
     *
     * @param Throwable $e Zachycená výjimka
     * @return void
     */
    public static function handle(Throwable $e): void
    {
        // TODO: [MAINTENANCE] Přidat ignorování určitých typů výjimek (např. UserException)
        LoggerHolder::get()->error(
            $e->getMessage(),
            [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]
        );

        // TODO: [FEATURE] Rozlišovat HTTP status podle typu výjimky (404, 403, 500)
        // 2️⃣ HTTP status
        http_response_code(500);

        // 3️⃣ výstup uživateli
        if (self::isDev()) {
            self::renderDev($e);
        } else {
            self::renderProd();
        }

        exit;
    }

    /**
     * Určuje, zda je aplikace ve vývojovém prostředí.
     * Rozhoduje o množství informací zobrazených uživateli.
     *
     * TODO: [CONFIG] Přidat konfigurační proměnnou pro detaily chyb (např. 'app.debug')
     * TODO: [SECURITY] V produkci NIKDY nezobrazovat stack trace ani file paths
     *
     * @return bool TRUE pokud je aplikace ve vývojovém prostředí
     */
    private static function isDev(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
    }

    /**
     * Vytvoří produkční chybovou stránku bez citlivých informací.
     * Minimalistické zobrazení pro koncové uživatele.
     *
     * TODO: [UX] Přidat odkazy na help, kontaktní formulář nebo návrat na homepage
     * TODO: [I18N] Přidat lokalizaci chybových hlášek podle jazyka uživatele
     *
     * @return void
     */
    private static function renderProd(): void
    {
        // TODO: [UX] Použít profesionální HTML šablonu s logem a navigací
        echo 'Došlo k chybě aplikace. Omlouváme se.';
    }

    /**
     * Vytvoří vývojovou chybovou stránku s podrobnými informacemi.
     * Zobrazuje stack trace, soubor, řádek a další debugging informace.
     *
     * TODO: [DEV] Přidat syntax highlighting pro stack trace
     * TODO: [DEV] Přidat zobrazení aktuálního stavu proměnných a session
     *
     * @param Throwable $e Výjimka k zobrazení
     * @return void
     */
    private static function renderDev(Throwable $e): void
    {
        echo '<h1>Application error</h1>';
        echo '<pre>';
        echo get_class($e) . "\n";
        echo $e->getMessage() . "\n\n";
        echo $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo $e->getTraceAsString();
        echo '</pre>';
    }
}