<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']['id']);
    }

public static function can(string $permission): bool
{
    return in_array($permission, $_SESSION['permissions'] ?? [], true);
}

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        static $cached = null;

        if ($cached === null) {
            $model  = new UserModel();
            $cached = $model->findById(self::id());
        }

        return $cached;
    }


    public static function logout(): void
    {
        unset($_SESSION['user'], $_SESSION['permissions']);
    }
    
public static function hasRole(array $roles): bool
{
    if (!self::check()) {
        return false;
    }

    return in_array(self::role(), $roles, true);
}
public static function id(): ?int
{
    return $_SESSION['user']['id'] ?? null;
}

public static function role(): ?string
{
    return $_SESSION['user']['global_role'] ?? null;
}

public static function name(): ?string
{
    if (!self::check()) {
        return null;
    }

    return trim(
        ($_SESSION['user']['first_name'] ?? '') . ' ' .
        ($_SESSION['user']['last_name'] ?? '')
    );
}

public static function label(): ?string
{
    if (!self::check()) {
        return null;
    }

    $name = self::name();
    $role = self::role();

    return $name
        ? "$name ($role)"
        : ucfirst((string) $role);
}

}
