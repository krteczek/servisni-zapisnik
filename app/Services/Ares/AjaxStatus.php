<?php

declare(strict_types=1);

namespace App\Services\Ares;

use App\Core\Session;

final class AjaxStatus
{
    private const SESSION_KEY = 'ajax';

    private static function count(): int
    {
        return (int) (Session::get(self::SESSION_KEY) ?? 0);
    }

    public static function set(): void
    {
        Session::set(
            self::SESSION_KEY,
            self::count() + 1
        );
    }

    public static function peek(): bool
    {
        return self::count() > 0;
    }

    public static function consume(): bool
    {
        $count = self::count();

        if ($count < 1) {
            return false;
        }

        if ($count === 1) {
            Session::forget(self::SESSION_KEY);

            return true;
        }

        Session::set(
            self::SESSION_KEY,
            $count - 1
        );

        return true;
    }
}