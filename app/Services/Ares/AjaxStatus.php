<?php

declare(strict_types=1);

namespace App\Services\Ares;

use App\Core\Session;

final class AjaxStatus
{
    private const SESSION_KEY = 'ajax';

    public static function set(): void
    {
        Session::set(self::SESSION_KEY, true);
    }

    public static function consume(): bool
    {
        if (Session::get(self::SESSION_KEY) !== true) {
            return false;
        }

        Session::forget(self::SESSION_KEY);

        return true;
    }
}