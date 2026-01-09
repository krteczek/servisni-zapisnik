<?php
declare(strict_types=1);

/**

PDO je globálně nastaveno na FETCH_ASSOC.
V modelech se nikdy fetch mód nespecifikuje.

**/

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require __DIR__ . '/../Config/database.php';
            self::$pdo = new PDO(
                $cfg['dsn'],
                $cfg['user'],
                $cfg['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
        return self::$pdo;
    }
}