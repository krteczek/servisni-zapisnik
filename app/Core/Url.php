<?php
// app/Core/Url.php
declare(strict_types=1);

namespace App\Core;

class Url
{
private static ?string $basePath = null;

public static function init(): void
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
        return self::$basePath . '/' . ltrim($path, '/');
    }

    public static function current(): string
    {
        return strtok($_SERVER['REQUEST_URI'], '?');
    }

    public static function isSection(string $prefix): bool
    {
        return str_starts_with(self::current(), self::to($prefix));
    }
}
