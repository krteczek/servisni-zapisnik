<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class Database
{
    /** @var array<string, PDO> */
    private static array $connections = [];

    private static ?string $workDbName = null;
    private static string $prefix = '';

    /* =========================
       PUBLIC API (LEGACY)
       ========================= */

    /**
     * ZPĚTNÁ KOMPATIBILITA
     * - pokud je zvolená work DB → work()
     * - jinak admin()
     */
    public static function pdo(): PDO
    {
        if (self::$workDbName !== null) {
            return self::work();
        }

        return self::admin();
    }

    public static function table(string $name): string
    {
        self::pdo(); // init + prefix
        return self::$prefix . $name;
    }

    /* =========================
       CONTEXT
       ========================= */

    public static function useWorkDatabase(string $dbName): void
    {
        self::$workDbName = $dbName;
    }

    public static function hasWorkDatabase(): bool
    {
        return self::$workDbName !== null;
    }

    /* =========================
       ADMIN DB
       ========================= */

    public static function admin(): PDO
    {
        return self::connect('admin');
    }

    /* =========================
       WORK DB
       ========================= */

    public static function work(): PDO
    {
        if (!self::$workDbName) {
            throw new RuntimeException('WORK database is not selected');
        }

        return self::connect(self::$workDbName);
    }

    /* =========================
       INTERNAL
       ========================= */

    private static function connect(string $dbName): PDO
    {
        if (isset(self::$connections[$dbName])) {
            return self::$connections[$dbName];
        }

        $config = Config::get('database');

        // prefix bereme jen jednou (legacy chování)
        self::$prefix = (string)($config['prefix'] ?? '');

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $dbName,
            $config['charset']
        );

        $pdo = new PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        self::$connections[$dbName] = $pdo;
        return $pdo;
    }

    public static function connection(string $name): PDO
    {
        return match ($name) {
            'admin' => self::admin(),
            'work'  => self::work(),
            default => throw new \RuntimeException("Unknown DB connection [$name]")
        };
    }
    
}