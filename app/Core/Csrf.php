<?php
declare(strict_types=1);

namespace App\Core;
use \App\Core\Session;
use \App\Core\Auth;

/**
 * Služba pro ochranu před Cross-Site Request Forgery (CSRF) útoky.
 * Generuje, validuje a spravuje CSRF tokeny uložené v uživatelské session.
 *
 * Implementuje timing-safe string comparison pro prevenci timing útoků.
 */
class Csrf
{
    /**
     * Klíč pro uložení CSRF tokenu v session.
     */
    private const KEY = '_csrf';

    // TODO: [UX] Zvážit per-form CSRF tokeny pro podporu více současně otevřených formulářů
    // TODO: [SECURITY] Zvážit implementaci same-site tokenů pro SPA a API

    /**
     * Vrátí aktuální CSRF token nebo vygeneruje nový pokud neexistuje.
     * Token je vázán na uživatelskou session a platí po celou dobu její životnosti.
     *
     * Vedlejší efekty:
     * - Generuje kryptograficky bezpečný náhodný token
     * - Ukládá token do session
     *
     * TODO: [SECURITY] Přidat expiraci tokenu po určité době (např. 30 minut)
     * TODO: [PERFORMANCE] Zvážit caching tokenu na úrovni requestu
     *
     * @return string CSRF token (64 hexadecimálních znaků)
     */
    public static function token(): string
    {
        Session::start();
        
        $token = Session::get(self::KEY);
        
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::KEY, $token);
        }

        return $token;
    }

    /**
     * Ověří, zda poskytnutý token odpovídá tokenu uloženému v session.
     * Používá timing-safe string comparison pro prevenci timing útoků.
     *
     * Očekává:
     * - Token v session existuje (pokud ne, vrátí false)
     * - Poskytnutý token je string (i prázdný)
     *
     * TODO: [SECURITY] Přidat rate limiting na ověřování tokenů
     * TODO: [AUDIT] Logovat neúspěšné pokusy o CSRF pro detekci útoků
     *
     * @param string $token Token k ověření (obvykle z $_POST['_token'])
     * @return bool TRUE pokud token je platný, jinak FALSE
     */
    public static function checkOLD(string $token): bool
    {
        Session::start();
        
        $storedToken = Session::get(self::KEY);
        
        if (empty($storedToken)) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }
    public static function check(string $token): bool
    {
        Session::start();
        
        $storedToken = (string) Session::get(self::KEY);
        
        if ($storedToken === '') {
            return false;
        }
        $out = hash_equals($storedToken, $token);
        if (!$out) {
            Logger::warning('CSRF validation failed', [
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'user_id' => Auth::id(),
            ]);
        }
        if ($out === true) {
            self::regenerate();
        }
        return $out;
    }
        
    /**
     * Alias pro metodu `check()` pro konzistentní API.
     *
     * @param string $token Token k ověření
     * @return bool TRUE pokud token je platný
     */
    public static function verify(string $token): bool
    {
        return self::check($token);
    }
    
    /**
     * Zneplatní aktuální CSRF token odstraněním ze session.
     * Používá se po úspěšném ověření pro prevenci replay útoků.
     *
     * Vedlejší efekty:
     * - Odstraní CSRF token ze session
     *
     * TODO: [SECURITY] Zvážit, zda invalidovat token po každém použití nebo nechat pro více requestů
     *
     * @return void
     */
    public static function invalidate(): void
    {
        Session::forget(self::KEY);
    }
    
    /**
     * Vygeneruje nový CSRF token a zneplatní starý.
     * Používá se pro rotaci tokenů nebo po odhlášení.
     *
     * Vedlejší efekty:
     * - Odstraní starý token ze session
     * - Vytvoří a uloží nový token
     *
     * @return string Nový CSRF token
     */
    public static function regenerate(): string
    {
        self::invalidate();
        return self::token();
    }
    
    /**
     * Vrátí HTML hidden input s aktuálním CSRF tokenem.
     * Pro použití ve formulářích.
     *
     * TODO: [SECURITY] Přidat autocomplete="off" pro prevenci autofill
     * TODO: [FEATURE] Přidat možnost přizpůsobit atributy inputu (class, id)
     *
     * @return string HTML kód `<input type="hidden" name="_token" value="...">`
     */
    public static function getField(): string
    {
        return sprintf(
            '<input type="hidden" name="_token" value="%s">',
            e(self::token())
        );
    }
}