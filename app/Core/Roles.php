<?php
declare(strict_types=1);

namespace App\Core;

final class Roles
{
    private static ?array $roles = null;

    public static function all(): array
    {
        if (self::$roles === null) {
            self::$roles = Config::get('roles')['roles'];
        }

        return self::$roles;
    }

    public static function default(): string
    {
        return Config::get('roles')['default'];
    }

    public static function exists(string $role): bool
    {
        return isset(self::all()[$role]);
    }
}
