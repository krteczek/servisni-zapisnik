<?php
declare(strict_types=1);

namespace App\Core;
/**
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
**/

class Csrf
{
    private const KEY = '_csrf_';

    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function check(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION[self::KEY])) {
            return false;
        }

        $isValid = hash_equals($_SESSION[self::KEY], $token);

        // token můžeš po kontrole zneplatnit:
        // unset($_SESSION[self::KEY]);

        return $isValid;
    }
}
