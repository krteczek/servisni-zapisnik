<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

class Transaction
{
    public static function run(callable $callback, string $connection = 'work')
    {
        $db = Database::connection($connection);

        try {
            $db->beginTransaction();

            $result = $callback();

            $db->commit();

            return $result;

        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $e; // necháš to bublat nahoru
        }
    }
}