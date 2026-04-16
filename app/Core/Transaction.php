<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;
use App\Core\Database;
use App\Core\LoggerHolder;


class Transaction
{
    public static function run(callable $callback, string $connection = 'work')
    {
        $db = Database::connection($connection);

        try {
            $db->beginTransaction();

            $result = $callback($db); // 🔥 tady změna

            $db->commit();

            return $result;

        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

	        LoggerHolder::get()->error('Transaction.run failed', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),

				]);
        }
    }
}