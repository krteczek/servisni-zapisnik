<?php
declare(strict_types=1);

namespace App\Core;

final class Access
{
    /**
     * Mapování oprávnění → role
     * (jedno místo v celé aplikaci)
     */
    private const RULES = [
        'teams.edit' => ['admin', 'mistr'],
        'users.edit' => ['admin', 'mistr'],
        'users.password' => ['admin'],
    ];

    public static function can(string $ability): bool
    {
        if (!Auth::check()) {
            return false;
        }

        if (!isset(self::RULES[$ability])) {
            return false; // nebo true, podle filozofie
        }

        return Auth::hasRole(self::RULES[$ability]);
    }
}
