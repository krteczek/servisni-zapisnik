<?php
declare(strict_types=1);

namespace App\Services\Ares;

use App\Core\Session;

final class AresSession
{
    private const KEY = 'company_ares';

    public static function set(string $ico, array $data): void
    {
        Session::set(self::KEY, [
            'ico'       => $ico,
            'loaded_at' => date('Y-m-d H:i:s'),
            'data'      => $data,
        ]);
    }

    public static function get(): ?array
    {
        $ares = Session::get(self::KEY);

        return is_array($ares) ? $ares : null;
    }

    public static function forget(): void
    {
        Session::forget(self::KEY);
    }
}