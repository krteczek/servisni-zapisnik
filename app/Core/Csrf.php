<?php
declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function check(string $token): bool
    {
        return hash_equals($_SESSION['_csrf'] ?? '', $token);
    }
}