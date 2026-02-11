<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Služba pro správu globálních uživatelských rolí v aplikaci.
 * Poskytuje přístup k definicím rolí, validaci a podporu pro efektivní role
 * (role-switching pro adminy s vyloučením root role).
 *
 * Třída implementuje caching konfigurace rolí pro optimalizaci výkonu.
 */
final class Roles
{
    /**
     * @var array|null Cache globálních rolí načtených z konfigurace
     */
    private static ?array $roles = null;

    // TODO: [MAINTENANCE] Přidat konstanty pro názvy klíčových rolí (ROOT, ADMIN, USER)
    // TODO: [FEATURE] Přidat podporu pro dědičnost rolí (role inheritance)

    /**
     * Vrátí všechny definované globální role.
     * Používá lazy loading s cache na úrovni requestu.
     *
     * Očekává:
     * - Konfigurační klíč 'roles.roles' existuje a obsahuje asociativní pole
     *
     * TODO: [CONFIG] Přidat validaci struktury konfigurace při boot aplikace
     * TODO: [PERFORMANCE] Zvážit vrácení immutable kolekce pro prevenci modifikací
     *
     * @return array Asociativní pole globálních rolí [role_key => role_label]
     */
    public static function all(): array
    {
        if (self::$roles === null) {
            self::$roles = Config::get('roles')['roles'];
        }

        return self::$roles;
    }

    /**
     * Vrátí výchozí globální roli pro nové uživatele.
     * Obvykle se jedná o nejnižší úroveň oprávnění (např. 'user').
     *
     * Očekává:
     * - Konfigurační klíč 'roles.default' existuje a obsahuje platný klíč role
     *
     * TODO: [SECURITY] Validovat, že default role existuje v seznamu rolí
     *
     * @return string Klíč výchozí globální role
     */
    public static function default(): string
    {
        return Config::get('roles')['default'];
    }

    /**
     * Ověří existenci globální role v definovaných rolích.
     *
     * TODO: [SECURITY] Přidat sanitizaci vstupu (trim, lowercase) pro konzistenci
     *
     * @param string $role Klíč role k ověření
     * @return bool TRUE pokud role existuje, jinak FALSE
     */
    public static function exists(string $role): bool
    {
        return isset(self::all()[$role]);
    }
    
    /**
     * Vrátí pole rolí dostupných pro role-switching.
     * Odstraňuje root roli ze seznamu, protože root nemůže přepínat na jiné role.
     *
     * Používá se pro:
     * - Administrátorský mód role-switching
     * - Validaci při přepínání rolí
     * - Zobrazení dostupných rolí v UI
     *
     * TODO: [FEATURE] Přidat konfiguraci, které role lze přepínat (např. admin→manager, admin→user)
     * TODO: [SECURITY] Zvážit odstranění dalších systémových rolí (např. 'system')
     *
     * @return array Asociativní pole efektivních rolí bez root role
     */
    public static function effective(): array
    {
        return array_diff_key(
            self::all(),
            ['root' => true]
        );
    }
}