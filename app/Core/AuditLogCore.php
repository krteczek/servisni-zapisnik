<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AuditLogModel;
use Throwable;

final class AuditLogCore
{
    /* =========================================================
       PUBLIC API
       ========================================================= */

    public static function logInsert(
        string $table,
        ?int $recordId = null,
        ?array $after = null
    ): void {
        if (!self::isAuditable($table)) {
            return;
        }

        try {
            if (!$after) {
                return;
            }

            unset($after['password_hash']);

            $diff = [];

            foreach ($after as $key => $value) {
                $diff[$key] = [
                    'from' => null,
                    'to'   => $value,
                ];
            }

            self::insertLog([
                'action'    => 'insert',
                'entity'    => $table,
                'entity_id' => $recordId,
                'diff'      => $diff,
            ]);

        } catch (Throwable $e) {
            self::logError($e);
        }
    }

    public static function logUpdate(
        string $table,
        int $recordId,
        array $before,
        array $after
    ): void {
        if (!self::isAuditable($table)) {
            return;
        }

        try {
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

            self::insertLog([
                'action'    => 'update',
                'entity'    => $table,
                'entity_id' => $recordId,
                'diff'      => $diff,
            ]);

        } catch (Throwable $e) {
            self::logError($e);
        }
    }

    /* =========================================================
       INTERNAL HELPERS
       ========================================================= */

    private static function isAuditable(string $table): bool
    {
        $config = Config::get('audit');

        // explicitní ignor má přednost
        if (in_array($table, $config['ignores'], true)) {
            return false;
        }

        // whitelist
        return in_array($table, $config['auditables'], true);
    }

    private static function insertLog(array $data): void
    {
        (new AuditLogModel())->insert([
            'user_id'    => Auth::id(),
            'action'     => $data['action'],
            'entity'     => $data['entity'],
            'entity_id'  => $data['entity_id'],
            'diff'       => json_encode(
                $data['diff'],
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ),
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

    private static function logError(Throwable $e): void
    {
        file_put_contents(
            __DIR__ . '/../../storage/audit_errors.log',
            sprintf(
                "[%s] %s\n",
                date('Y-m-d H:i:s'),
                $e->getMessage()
            ),
            FILE_APPEND
        );
    }
}
