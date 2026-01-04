<?php
declare(strict_types=1);

namespace App\Core;

class Session
{
    private static bool $started = false;

    /* =========================
       START / REGENERACE
       ========================= */

    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        self::$started = true;
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /* =========================
       GET / SET / FORGET
       ========================= */

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();

        $value = $_SESSION;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();

        $segments = explode('.', $key);
        $ref = &$_SESSION;

        foreach ($segments as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }

        $ref = $value;
    }

    public static function forget(string $key): void
    {
        self::start();

        $segments = explode('.', $key);
        $ref = &$_SESSION;

        while (count($segments) > 1) {
            $segment = array_shift($segments);

            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                return;
            }

            $ref = &$ref[$segment];
        }

        unset($ref[array_shift($segments)]);
    }

    /* =========================
       HELPERY
       ========================= */

    public static function has(string $key): bool
    {
        self::start();
        return self::get($key, '__missing__') !== '__missing__';
    }

    public static function all(): array
    {
        self::start();
        return $_SESSION;
    }

    public static function flush(): void
    {
        self::start();
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        self::start();
        session_destroy();
        self::$started = false;
    }
}
