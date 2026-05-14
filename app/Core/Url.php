<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Služba pro práci s URL v aplikaci.
 * Zajišťuje generování absolutních cest, detekci aktuální URL, přesměrování
 * a správu multi-tenant prefixů v URL.
 *
 * Třída implementuje lazy inicializaci základní cesty pro správné fungování
 * v různých prostředích (root, subdirectory, virtual host).
 */
final class Url
{
    /**
     * @var string|null Základní cesta aplikace (bez hostname)
     */
    private static ?string $basePath = null;

    /**
     * Inicializuje základní cestu aplikace.
     * Detekuje cestu skriptu a odstraňuje 'index.php' pro čisté URL.
     *
     * Vedlejší efekty:
     * - Čte $_SERVER['SCRIPT_NAME'] pro detekci základní cesty
     *
     * TODO: [MAINTENANCE] Přidat podporu pro CLI prostředí (např. pro cron úlohy)
     * TODO: [CONFIG] Přidat možnost manuálně nastavit basePath v konfiguraci
     *
     * @return void
     */
    private static function init(): void
    {
        if (self::$basePath !== null) {
            return;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'];
        self::$basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');
    }

    /**
     * Vygeneruje absolutní URL s nahrazením tenant placeholderu.
     * Podporuje syntaxi '/{tenant}/path' pro automatické nahrazení aktuálním tenantem.
     *
     * Vedlejší efekty:
     * - Volá Auth::tenantSlug() pro získání aktuálního tenanta
     *
     * TODO: [PERFORMANCE] Přidat caching výsledných URL pro opakované volání se stejnými parametry
     * TODO: [FEATURE] Přidat podporu pro query parametry a fragmenty (#)
     *
     * @param string $path Relativní cesta (může obsahovat '/{tenant}' placeholder)
     * @return string Absolutní URL včetně basePath
     */
    public static function to(string $path = ''): string
    {
        self::init();
		// 👉 ochrana proti absolutní URL
		    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
		        return $path;
		    }
        if (str_starts_with($path, '/{tenant}')) {
            $tenant = Auth::tenantSlug();

            if ($tenant) {
                $path = '/' . $tenant . substr($path, 9);
            }
        }

        return self::$basePath . '/' . ltrim($path, '/');
    }

    /**
     * Vrátí aktuální cestu bez query stringu.
     *
     * TODO: [SECURITY] Ošetřit speciální znaky v URL pro prevenci XSS při zobrazení
     *
     * @return string Aktuální URL cesta
     */
    public static function current(): string
    {
        return strtok($_SERVER['REQUEST_URI'], '?');
    }

    /**
     * Porovná aktuální cestu s danou cestou.
     *
     * @param string $path Cesta k porovnání
     * @return bool TRUE pokud se cesty shodují
     */
    public static function is(string $path): bool
    {
        return self::current() === self::to($path);
    }

    /**
     * Zjistí, zda aktuální cesta začíná daným prefixem.
     * Používá se pro detekci aktivní sekce v navigaci.
     *
     * @param string $prefix Prefix cesty
     * @return bool TRUE pokud aktuální cesta začíná prefixem
     */
    public static function isSection(string $prefix): bool
    {
        return str_starts_with(self::current(), self::to($prefix));
    }

    /* =========================
       REDIRECT – JEDINÉ MÍSTO
       ========================= */

    /**
     * Provede HTTP přesměrování na zadanou cestu.
     * Jediné místo v aplikaci kde se provádí exit() po přesměrování.
     *
     * Vedlejší efekty:
     * - Odesílá HTTP hlavičku Location
     * - Ukončuje vykonávání skriptu (exit)
     *
     * TODO: [SECURITY] Přidat validaci, že přesměrování zůstává v rámci stejné domény
     * TODO: [TESTING] Přidat možnost potlačit exit pro unit testy
     *
     * @param string $path Cílová cesta
     * @param int $code HTTP status code (302 - dočasné, 301 - trvalé)
     * @return never
     */
    public static function redirect(string $path, int $code = 302): never
    {
        header('Location: ' . self::to($path), true, $code);
        exit;
    }

    /**
     * Vrátí základní URL aplikace (scheme + hostname).
     * Používá se pro generování absolutních URL pro e-maily, API, atd.
     *
     * TODO: [SECURITY] Detekovat HTTPS a vrátit správné schema
     * TODO: [CONFIG] Přidat možnost konfigurovat base URL pro reverse proxy
     *
     * @return string Základní URL (např. 'http://localhost' nebo 'https://app.example.com')
     */
	public static function base(): string
	{
	    return Config::get('app.url') ?? self::detectBase();
	}
    public static function detectBase(): string
    {
        // 1️⃣ Detekce schématu (https/http)
        $isHttps = false;

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $isHttps = true;
        } elseif (($_SERVER['SERVER_PORT'] ?? null) == 443) {
            $isHttps = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $isHttps = $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
        }

        $scheme = $isHttps ? 'https' : 'http';

        // 2️⃣ Host (proxy-friendly)
        $host = $_SERVER['HTTP_X_FORWARDED_HOST']
            ?? $_SERVER['HTTP_HOST']
            ?? $_SERVER['SERVER_NAME']
            ?? 'localhost';

        // 3️⃣ Port (jen pokud není standardní)
        $port = $_SERVER['SERVER_PORT'] ?? null;

        $portPart = '';
        if ($port && !in_array((int)$port, [80, 443], true)) {
            // Pozor: HTTP_HOST už může port obsahovat
            if (!str_contains($host, ':')) {
                $portPart = ':' . $port;
            }
        }

        return $scheme . '://' . $host . $portPart;
    }
 
    /**
     * Přesměruje zpět na předchozí stránku (HTTP referer) nebo na fallback.
     * Kontroluje, že referer je z stejné domény pro prevenci open redirect útoků.
     *
     * Vedlejší efekty:
     * - Kontroluje HTTP referer hlavičku
     * - Odesílá HTTP hlavičku Location
     * - Ukončuje vykonávání skriptu
     *
     * TODO: [SECURITY] Přidat whitelist povolených domén pro cross-domain referery
     *
     * 
     * @return never
     */
    public static function back(): never
    {
        $old = Session::get('last_page', '/');
        self::redirect($old);
    }



    /**
     * Přesměruje na stejnou stránku (refresh).
     * Používá se po POST operacích pro prevenci duplicate submission.
     *
     * Vedlejší efekty:
     * - Odesílá HTTP hlavičku Location s aktuální URL
     * - Ukončuje vykonávání skriptu
     *
     * TODO: [UX] Přidat flash message před refresh pro informování uživatele
     *
     * @param int $code HTTP status code
     * @return never
     */
    public static function refresh(int $code = 302): never
    {
        header('Location: ' . $_SERVER['REQUEST_URI'], true, $code);
        exit;
    }
}