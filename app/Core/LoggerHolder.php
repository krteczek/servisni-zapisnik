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
            // fallback – NullLogger
            return new class implements LoggerInterface {
                public function emergency(string $message, array $context = []): void {}
                public function alert(string $message, array $context = []): void {}
                public function critical(string $message, array $context = []): void {}
                public function error(string $message, array $context = []): void {}
                public function warning(string $message, array $context = []): void {}
                public function notice(string $message, array $context = []): void {}
                public function info(string $message, array $context = []): void {}
                public function debug(string $message, array $context = []): void {}
                public function log(string $level, string $message, array $context = []): void {}
            };
        }

        return self::$logger;
    }
}
