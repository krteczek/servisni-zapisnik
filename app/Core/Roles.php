<?php
declare(strict_types=1);

namespace App\Core;

final class Roles
{
    public static function all(): array
    {
        return Config::get('roles')['roles'];
    }

    public static function default(): string
    {
        return Config::get('roles')['default'];
    }

    public static function exists(string $role): bool
    {
        return array_key_exists($role, self::all());
    }
}
