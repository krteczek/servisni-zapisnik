<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

class DatabaseScope
{
    public static function work(string $dbName, callable $callback): mixed
    {
        $previous = Database::getCurrentDatabase();

        try {
            Database::useWorkDatabase($dbName);

            return $callback();

        } finally {
            Database::setDatabase($previous);
        }
    }

    public static function admin(callable $callback): mixed
    {
        $previous = Database::getCurrentDatabase();

        try {
            Database::useAdminDatabase();

            return $callback();

        } finally {
            Database::setDatabase($previous);
        }
    }
}