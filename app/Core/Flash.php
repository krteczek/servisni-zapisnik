<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Session;
final class Flash
{
    public static function success(string $msg): void
    {
        Session::flash('success', $msg);
    }

    public static function error(string $msg): void
    {
        Session::flash('error', $msg);
    }

    public static function info(string $msg): void
    {
        Session::flash('info', $msg);
    }
}
