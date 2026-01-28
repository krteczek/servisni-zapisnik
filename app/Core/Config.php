<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $path] = self::parseKey($key);

        if (!isset(self::$cache[$file])) {
            $configPath = __DIR__ . '/../Config/' . $file . '.php';

            if (!is_file($configPath)) {
                throw new \RuntimeException("Config soubor {$file} neexistuje");
            }

            self::$cache[$file] = require $configPath;
        }

        $value = self::$cache[$file];

        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private static function parseKey(string $key): array
    {
        $parts = explode('.', $key);
        $file  = array_shift($parts);

        return [$file, $parts];
    }
}
