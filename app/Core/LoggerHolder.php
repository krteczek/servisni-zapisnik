<?php
declare(strict_types=1);

namespace App\Core;

final class LoggerHolder
{
    private static ?LoggerInterface $logger = null;

    public static function set(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function get(): LoggerInterface
    {
        if (self::$logger === null) {
            throw new \RuntimeException('Logger not initialized');
        }

        return self::$logger;
    }
}
