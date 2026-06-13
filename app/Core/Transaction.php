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
        $isOuter = false;

        try {
            
            if (!$db->inTransaction()) {
                $db->beginTransaction();
                $isOuter = true;
            } else {
                $isOuter = false;
            }

            $result = $callback($db); // 🔥 tady změna

            if ($result === false) {
                throw new \RuntimeException('Transaction callback returned false');
            }

            if ($isOuter) {
                $db->commit();
            }
            
            return $result;

        } catch (Throwable $e) {
             if ($isOuter && $db->inTransaction()) {
                $db->rollBack();
            }

	        LoggerHolder::get()->error('Transaction.run failed', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),
                    'connection' => $connection,
                    'isOuter'    => $isOuter,

				]);

            throw $e; // 🔥 KRITICKÉ
        }
    }
}