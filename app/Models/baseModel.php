<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    /**
     * Logický název tabulky (bez prefixu)
     * musí ho definovat každý model
     */
    protected static string $table;

    /**
     * PDO – jednotný přístup
     */
    protected static function db(): PDO
    {
        return Database::pdo();
    }

    /**
     * Vrátí název tabulky včetně prefixu
     * - self::table()      -> vlastní tabulka modelu
     * - self::table('users') -> cizí tabulka
     */
    protected static function table(?string $table = null): string
    {
        $config = Database::config();
        $prefix = $config['table_prefix'] ?? '';

        $name = $table ?? static::$table;

        return $prefix . $name;
    }
}