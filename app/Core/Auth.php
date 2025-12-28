<?php
declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            echo '403 – Nemáš oprávnění';
            exit;
        }
    }

    public static function logout(): void
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}