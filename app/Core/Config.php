<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $cache = [];

    public static function get(string $file): array
    {
        if (!isset(self::$cache[$file])) {
            $path = __DIR__ . '/../Config/' . $file . '.php';

            if (!is_file($path)) {
                throw new \RuntimeException("Config soubor {$file} neexistuje");
            }

            self::$cache[$file] = require $path;
        }

        return self::$cache[$file];
    }
}
