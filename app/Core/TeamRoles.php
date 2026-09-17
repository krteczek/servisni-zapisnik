<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Služba pro práci s týmovými rolemi s robustní validací konfigurace.
 * Implementuje caching konfigurace a runtime kontrolu validity rolí.
 *
 * Třída zajišťuje, že konfigurace rolí je vždy validní a konzistentní.
 * Používá lazy loading konfigurace s jednorázovou validací.
 * @phpstan-import-type MenuRouteRow from Types
 */
final class TeamRoles
{
    /**
     * @var array<string, mixed>|null Cache načtené a validované konfigurace rolí
     */
    private static ?array $config = null;

    /**
     * Načte a validuje konfiguraci týmových rolí.
     * Vyhodí výjimku pokud je konfigurace neplatná nebo neúplná.
     *
     * Očekává:
     * - Konfigurační klíč 'roles_in_team' existuje
     * - Obsahuje klíče 'roles' (array) a 'default' (string)
     * - Default role existuje v poli roles
     *
     * TODO: [MAINTENANCE] Přidat logování při změně konfigurace rolí
     * TODO: [FEATURE] Přidat podporu pro hierarchii rolí s validací cyklů
     *
     * @throws \RuntimeException Pokud je konfigurace neplatná
     * @return array<string, mixed> Validovaná konfigurace rolí
     */
    private static function config(): array
    {
        if (self::$config === null) {
            $config = Config::get('roles_in_team');

            if (
                !is_array($config)
                || !isset($config['roles'], $config['default'])
                || !is_array($config['roles'])
            ) {
                throw new \RuntimeException('Invalid roles_in_team configuration');
            }

            self::$config = $config;
        }

        return self::$config;
    }

    /**
     * Vrátí všechny definované týmové role.
     * Garantuje, že vrácené pole je vždy validní a konzistentní.
     *
     * TODO: [PERFORMANCE] Zvážit vrácení kopie pole pro prevenci modifikací
     * TODO: [FEATURE] Přidat metodu pro získání rolí seřazených podle priority/úrovně
     *
     * @return array<string, mixed> Asociativní pole rolí [role_key => role_label]
     */
    public static function all(): array
    {
        return self::config()['roles'];
    }

    /**
     * Vrátí výchozí roli pro nové členy týmu.
     * Validuje, že výchozí role skutečně existuje v seznamu rolí.
     *
     * @throws \RuntimeException Pokud výchozí role není definována
     * @return string Klíč výchozí role
     */
    public static function default(): string
    {
        $default = self::config()['default'];

        if (!self::exists($default)) {
            throw new \RuntimeException("Default role '{$default}' is not defined");
        }

        return $default;
    }

    /**
     * Ověří existenci role v definovaných týmových rolích.
     * Kontroluje i prázdný string pro prevenci chyb.
     *
     * TODO: [SECURITY] Přidat validaci formátu role (pouze alfanumerické a podtržítko)
     * TODO: [FEATURE] Přidat metodu pro získání popisku role s fallback na klíč
     *
     * @param string $role Klíč role k ověření
     * @return bool TRUE pokud role existuje a není prázdná, jinak FALSE
     */
    public static function exists(string $role): bool
    {
        return $role !== '' && array_key_exists($role, self::config()['roles']);
    }
}