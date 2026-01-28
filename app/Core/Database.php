<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    /** @var array<string, PDO> */
    private static array $connections = [];

    /** @var string|null */
    private static ?string $currentWorkDb = null;

    /**
     * ADMIN DB – vždy jedna
     */
    public static function admin(): PDO
    {
        return self::getConnection('admin');
    }

    /**
     * WORK DB – podle loginu / domény
     */
    public static function work(): PDO
    {
        if (self::$currentWorkDb === null) {
            throw new RuntimeException('WORK database is not selected (missing useWorkDatabase()).');
        }

        return self::getConnection('work:' . self::$currentWorkDb);
    }

    /**
     * Nastaví aktivní WORK DB
     */
    public static function useWorkDatabase(string $dbName): void
    {
        self::$currentWorkDb = $dbName;
    }

    /**
     * Legacy kompatibilita – směřuje na WORK pokud existuje, jinak ADMIN
     */
    public static function pdo(): PDO
    {
        return self::$currentWorkDb !== null
            ? self::work()
            : self::admin();
    }

    /**
     * Interní factory
     */
    private static function getConnection(string $key): PDO
    {
        if (isset(self::$connections[$key])) {
            return self::$connections[$key];
        }

        try {
            if ($key === 'admin') {
                $cfg = Config::get('database.admin');
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['dbname']);
                $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], self::options());
            } elseif (str_starts_with($key, 'work:')) {
                $dbName = substr($key, 5);
                $cfg = Config::get('database.work');
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $dbName);
                $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], self::options());
            } else {
                throw new RuntimeException('Unknown database key: ' . $key);
            }
        } catch (PDOException $e) {
            Logger::error('DB connection failed', [
                'key' => $key,
                'exception' => $e,
            ]);
            throw new RuntimeException('Database connection failed.');
        }

        self::$connections[$key] = $pdo;
        return $pdo;
    }

    private static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
}
