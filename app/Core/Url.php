<?php
declare(strict_types=1);

namespace App\Core;

final class Url
{
    private static ?string $basePath = null;

    private static function init(): void
    {
        if (self::$basePath !== null) {
            return;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'];
        self::$basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');
    }

public static function to(string $path = ''): string
{
    self::init();

    if (str_starts_with($path, '/{tenant}')) {
        $tenant = Auth::tenantSlug();

        if ($tenant) {
            $path = '/' . $tenant . substr($path, 9);
        }
    }

    return self::$basePath . '/' . ltrim($path, '/');
}

    public static function current(): string
    {
        return strtok($_SERVER['REQUEST_URI'], '?');
    }

    public static function is(string $path): bool
    {
        return self::current() === self::to($path);
    }

    public static function isSection(string $prefix): bool
    {
        return str_starts_with(self::current(), self::to($prefix));
    }

    /* =========================
       REDIRECT – JEDINÉ MÍSTO
       ========================= */

    public static function redirect(string $path, int $code = 302): never
    {
        header('Location: ' . self::to($path), true, $code);
        exit;
    }
    public static function base(): string
    {
    		return 'http://' . $_SERVER["HTTP_HOST"];
    }
public static function back(string $fallback = '/'): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? null;

    if ($referer && str_starts_with($referer, self::to(''))) {
        header('Location: ' . $referer);
    } else {
        self::redirect($fallback);
    }

    exit;
}
public static function refresh(int $code = 302): never
{
    header('Location: ' . $_SERVER['REQUEST_URI'], true, $code);
    exit;
}

}
