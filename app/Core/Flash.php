<?php
declare(strict_types=1);

namespace App\Core;

class Flash
{
    public static function add(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    public static function get(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}
