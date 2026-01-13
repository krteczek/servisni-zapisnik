<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;
    private static string $prefix = '';

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $config = Config::get('database');

            self::$pdo = new PDO(
                $config['dsn'],
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            self::$prefix = (string)($config['prefix'] ?? '');
        }

        return self::$pdo;
    }

    public static function table(string $name): string
    {
        self::pdo(); // init
        return self::$prefix . $name;
    }
}
