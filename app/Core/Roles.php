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
    private const ROLE_ROOT  = 'root';
    private const ROLE_ADMIN = 'admin';
    private const ROLE_MISTR = 'mistr';
    //private const ROLE_PREDAK  = 'predak';
    //private const ROLE_MONTER  = 'monter';
    /**
     * @var array<string, mixed>|null Cache načtené konfigurace rolí
     */
    private static ?array $roles = null;

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (self::$roles === null) {
            self::$roles = Config::get('roles.roles');
        }

        return self::$roles;
    }

    public static function default(): string
    {
        return Config::get('roles.default');
    }

    public static function exists(string $role): bool
    {
        return isset(self::all()[$role]);
    }

    /*
     |--------------------------------------------------------------------------
     | Semantické metody (žádné pole skupin)
     |--------------------------------------------------------------------------
     */

    public static function isRoot(string $role): bool
    {
        return $role === self::ROLE_ROOT;
    }

    public static function isAdmin(string $role): bool
    {
        return $role === self::ROLE_ADMIN;
    }

    public static function isMistr(string $role): bool
    {
        return $role === self::ROLE_MISTR;
    }

    public static function isManagement(string $role): bool
    {
        return $role === self::ROLE_ADMIN
            || $role === self::ROLE_MISTR;
    }

    /*
     |--------------------------------------------------------------------------
     | Doménové schopnosti (ne RBAC)
     |--------------------------------------------------------------------------
     */

    public static function canManageUsers(string $role): bool
    {
        return self::isAdmin($role);
    }

    public static function canSeeAllTasks(string $role): bool
    {
        return self::isManagement($role);
    }

    public static function canAddReportsGlobally(string $role): bool
    {
        return self::isManagement($role);
    }
    /**
     * @return array<string, mixed>
     */
    public static function effective(): array
    {
        $roles = self::all();
        unset($roles[self::ROLE_ROOT]);

        return $roles;
    }
}