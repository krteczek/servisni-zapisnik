<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AuditLogModel;
use Throwable;

final class AuditLogCore
{
    public static function logInsert(
        string $table,
        ?int $recordId = null,
        ?array $after = null
    ): void {
        try {
        	unset($after['password_hash']);

(new AuditLogModel())->insert([
    'user_id'    => Auth::id(),
    'action'     => 'insert',
    'entity'     => $table,
    'entity_id'  => $recordId,
    'diff' 		  => $after  ? json_encode($after,  JSON_UNESCAPED_UNICODE) : null,//Celý záznam do db
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
]);
        } catch (\Throwable $e) {
    file_put_contents(
        __DIR__ . '/../../storage/audit_errors.log',
        date('Y-m-d H:i:s') . ' ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );
}
    }

public static function logUpdate(
    string $table,
    int $recordId,
    array $before,
    array $after
): void {
	
		$passwordChanged = false;
		
		if (
		    isset($before['password_hash'], $after['password_hash']) &&
		    $before['password_hash'] !== $after['password_hash']
		) {
		    $passwordChanged = true;
		}
		
		unset($before['password_hash'], $after['password_hash']);
		
		$diff = self::diff($before, $after);
		
		if ($passwordChanged) {
		    $diff['password'] = [
		        'changed' => true,
		    ];
		}
		
		if ($diff === []) {
		    return;
		}

    (new AuditLogModel())->insert([
        'user_id'    => Auth::id(),
        'action'     => 'update',
        'entity'     => $table,
        'entity_id'  => $recordId,
        'diff'       => json_encode($diff, JSON_UNESCAPED_UNICODE),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);
}
    
    private static function diff(array $before, array $after): array
{
    $diff = [];

    foreach ($after as $key => $newValue) {
        $oldValue = $before[$key] ?? null;

        if ($oldValue !== $newValue) {
            $diff[$key] = [
                'from' => $oldValue,
                'to'   => $newValue,
            ];
        }
    }

    return $diff;
}

}
