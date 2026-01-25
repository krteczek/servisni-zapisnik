<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AccessLogModel;

final class AccessLogger
{
    public static function log(string $type): void
    {
        try {
            $model = new AccessLogModel();

            $userId = Auth::check() ? Auth::id() : null;
            $ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $model->log([
                'user_id'    => $userId,
                'ip_address' => $ip,
                'type'       => $type,
                'path'       => $_SERVER['REQUEST_URI'] ?? '',
                'method'     => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            self::detectAbuse($model, $type, $ip, $userId);

        } catch (\Throwable) {
            // logování NIKDY nesmí shodit aplikaci
        }
    }

    private static function detectAbuse(
        AccessLogModel $model,
        string $type,
        string $ip,
        ?int $userId
    ): void {
        if (!in_array($type, ['403', '404'], true)) {
            return;
        }

        $count = $model->countRecent($type, $ip, 10);

        if ($count >= 5 && $userId !== null) {
            Flash::add(
                'warning',
                'Bylo zaznamenáno opakované neplatné chování. '
                . 'Pokračování může vést k omezení účtu.'
            );
        }
    }
}
