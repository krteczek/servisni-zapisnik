<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Wrapper třída pro práci s PHP session s podporou dot notation a flash messages.
 * Zajišťuje správu session lifecycle a poskytuje bezpečné API pro přístup k session datům.
 *
 * Implementuje lazy inicializaci session a prevenci předčasného odeslání hlaviček.
 */
class Session
{
 

    // TODO: [SECURITY] Přidat konfiguraci session cookie parametrů (secure, httponly, samesite)
    // TODO: [PERFORMANCE] Zvážit session locking pro kritické sekce s paralelními requesty

    /* =========================
       START / REGENERACE
       ========================= */

    /**
     * Inicializuje session pokud již nebyla zahájena.
     * Implementuje lazy loading pro prevenci zbytečného session_start().
     *
     * Vedlejší efekty:
     * - Odesílá HTTP hlavičky pro session cookie
     * - Zamyká session soubor pro zápis
     *
     * TODO: [SECURITY] Přidat session_regenerate_id() po úspěšném přihlášení
     *
     * @return void
     */
public static function start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = ($_SERVER['HTTPS'] ?? null) === 'on';

    session_name($secure ? '__Host-PHPSESSID' : 'PHPSESSID');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $secure ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_only_cookies', '1');

    session_start();
}    


    /**
     * Regeneruje session ID pro prevenci session fixation útoků.
     * Odstraní stará session data a vytvoří nový identifikátor.
     *
     * Vedlejší efekty:
     * - Maže stará session data
     * - Generuje nové session ID
     * - Odesílá nové session cookie
     *
     * @return void
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /* =========================
       GET / SET / FORGET 
       ========================= */

    /**
     * Získá hodnotu z session pomocí dot notation (např. 'user.name').
     * Podporuje vnořená pole a poskytuje výchozí hodnotu pro neexistující klíče.
     *
     * TODO: [PERFORMANCE] Přidat caching čtených hodnot na úrovni requestu
     * TODO: [MAINTENANCE] Zvážit implementaci ArrayAccess rozhraní pro intuitivnější API
     *
     * @param string $key Klíč v dot notation formátu
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Hodnota z session nebo výchozí hodnota
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();

        $value = $_SESSION;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Uloží hodnotu do session pomocí dot notation.
     * Automaticky vytvoří potřebné vnořené pole struktury.
     *
     * Vedlejší efekty:
     * - Mění obsah $_SESSION superglobálu
     * - Ukládá data do session souboru/databáze
     *
     * TODO: [SECURITY] Přidat validaci klíčů pro prevenci path traversal
     *
     * @param string $key Klíč v dot notation formátu
     * @param mixed $value Hodnota k uložení
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();

        $segments = explode('.', $key);
        $ref = &$_SESSION;

        foreach ($segments as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }

        $ref = $value;
    }

    /**
     * Odstraní klíč (a jeho hodnotu) z session pomocí dot notation.
     * Bezpečně zpracovává neexistující klíče a vnořené struktury.
     *
     * @param string $key Klíč v dot notation formátu
     * @return void
     */
    public static function forget(string $key): void
    {
        self::start();

        $segments = explode('.', $key);
        $ref = &$_SESSION;

        while (count($segments) > 1) {
            $segment = array_shift($segments);

            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                return;
            }

            $ref = &$ref[$segment];
        }

        unset($ref[array_shift($segments)]);
    }

    /* =========================
       HELPERY
       ========================= */

    /**
     * Zjistí, zda klíč existuje v session.
     *
     * @param string $key Klíč v dot notation formátu
     * @return bool TRUE pokud klíč existuje, jinak FALSE
     */
    public static function has(string $key): bool
    {
        self::start();
        return self::get($key, '__missing__') !== '__missing__';
    }

    /**
     * Vrátí celý obsah session jako pole.
     * Pozor: vrací reference na $_SESSION, ne kopii.
     *
     * @return array Celý obsah session
     */
    public static function all(): array
    {
        self::start();
        return $_SESSION;
    }

    /**
     * Vyprázdní všechny session data ale zachová session ID.
     * Užitečné pro částečné odhlášení nebo reset stavu.
     *
     * Vedlejší efekty:
     * - Maže všechna data v $_SESSION
     *
     * @return void
     */
    public static function flush(): void
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Kompletně zničí session (data i cookie).
     * Používá se při úplném odhlášení uživatele.
     *
     * Vedlejší efekty:
     * - Maže session data
     * - Odstraňuje session cookie
     * - Resetuje internal stav
     *
     * TODO: [SECURITY] Přidat invalidaci session na serverové straně (pokud použito session store)
     *
     * @return void
     */

public static function destroy(): void
{
    self::start();
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Strict',
        ]);
    }

    session_destroy();

    // 🔥 důležité:
    session_write_close();
}

    
    /* =========================
       FLASH ZPRÁVY
       ========================= */

    /**
     * Uloží flash zprávu do session - dostupnou pouze pro další request.
     * Automaticky se smaže po přečtení.
     *
     * @param string $key Klíč flash zprávy
     * @param mixed $value Hodnota flash zprávy (obvykle string nebo array)
     * @return void
     */
    public static function flash(string $key, mixed $value): void
    {
        self::set('_flash.' . $key, $value);
    }

    /**
     * Zjistí, zda existuje flash zpráva s daným klíčem.
     *
     * @param string $key Klíč flash zprávy
     * @return bool TRUE pokud flash zpráva existuje
     */
    public static function hasFlash(string $key): bool
    {
        return self::has('_flash.' . $key);
    }

    /**
     * Získá a odstraní flash zprávu z session.
     * Implementuje "read-once" chování - zpráva je dostupná pouze při prvním čtení.
     *
     * Vedlejší efekty:
     * - Odstraňuje přečtenou flash zprávu z session
     *
     * @param string $key Klíč flash zprávy
     * @param mixed $default Výchozí hodnota pokud zpráva neexistuje
     * @return mixed Obsah flash zprávy nebo výchozí hodnota
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        if (!self::hasFlash($key)) {
            return $default;
        }

        $value = self::get('_flash.' . $key, $default);
        self::forget('_flash.' . $key);

        return $value;
    }
/**
 * Vygeneruje HTML pro všechny flash zprávy.
 * Zprávy se při čtení automaticky odstraní (read-once).
 */
public static function renderFlash(): string
{
    if (!self::has('_flash')) {
        return '';
    }

    $html = '';
    $flashes = self::get('_flash', []);

    foreach ($flashes as $key => $value) {

        $message = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

        $class = match ($key) {
            'success' => 'flash-success',
            'error'   => 'flash-error',
            'warning' => 'flash-warning',
            'info'    => 'flash-info',
            default   => 'flash-info',
        };

        $html .= "
<div class=\"flash-container\">
   <div class=\"flash {$class}\">{$message}</div>
</div>

";
    }

    self::forget('_flash');

    return $html;
}
}