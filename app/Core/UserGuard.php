<?php
declare(strict_types=1);

namespace App\Core;

final class UserGuard
{
    public static function isRoot(array $user): bool
    {
        return $user['global_role'] === 'root';
    }

    public static function isDomainAdmin(array $user): bool
    {
        return (int)($user['domain_admin'] ?? 0) === 1;
    }

    public static function isProtected(array $user): bool
    {
        return self::isRoot($user) || self::isDomainAdmin($user);
    }
}
