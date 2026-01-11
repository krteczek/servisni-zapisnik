<?php
declare(strict_types=1);

namespace App\Core;

final class TeamRoles
{
    public static function all(): array
    {
        return Config::get('roles_in_team')['roles'];
    }

    public static function default(): string
    {
        return Config::get('roles_in_team')['default'];
    }

    public static function exists(string $role): bool
    {
        return array_key_exists($role, self::all());
    }
}
